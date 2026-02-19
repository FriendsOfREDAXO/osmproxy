<?php
function deleteOSMCacheFiles($dir, $patterns = "*", int $timeout = 86400)
{
    foreach (glob($dir . "*" . "{{$patterns}}", GLOB_BRACE) as $f) {
        if (file_exists($f) && is_writable($f) && @filemtime($f) < (time() - $timeout))
            @unlink($f);
    }
}
$addon = rex_addon::get('osmproxy');
rex_dir::create($addon->getCachePath());

if (rex_get('osmtype', 'string')) {
    // DEBUG: Log request
    error_log('OSMProxy Request: osmtype=' . rex_get('osmtype', 'string') . ', z=' . rex_get('z', 'int') . ', x=' . rex_get('x', 'int') . ', y=' . rex_get('y', 'int'));
    
    // Clear REDAXO OutputBuffers
    rex_response::cleanOutputBuffers();
    clearstatcache();
    
    // Referer-Check: Für mapbox_style, mapbox_v4, mapbox_glyphs und vector_tiles deaktiviert (kommt von JavaScript)
    $type = rex_escape(rex_get('osmtype', 'string'));
    $refererHost = !empty($_SERVER['HTTP_REFERER']) ? parse_url($_SERVER['HTTP_REFERER'], PHP_URL_HOST) : null;
    if (!in_array($type, ['mapbox_style', 'mapbox_v4', 'mapbox_glyphs', 'vector_tiles']) && $refererHost && $refererHost != $_SERVER['HTTP_HOST']) {
        error_log('OSMProxy: Blocked - wrong referer');
        die();
    }
    
    // Variablen initialisieren
    $dir = $addon->getCachePath();
    $ttl = 86400;
    deleteOSMCacheFiles($dir,'*',$ttl);
    $x = rex_get('x', 'int');
    $y = rex_get('y', 'int');
    $z = rex_get('z', 'int');
    
    // Determine file extension and content type based on tile type
    $isMapboxStyle = ($type === 'mapbox_style');
    $isMapboxV4 = ($type === 'mapbox_v4');
    $isMapboxGlyphs = ($type === 'mapbox_glyphs');
    $isVectorTiles = ($type === 'vector_tiles');
    $isMapbox = ($isMapboxStyle || $isMapboxV4 || $isMapboxGlyphs);
    
    // Vector tiles können verschiedene Formate haben
    if ($isMapboxV4 || $isVectorTiles) {
        $format = rex_get('format', 'string', 'mvt');
        // Mapbox v4 API: immer .mvt verwenden für die Anfrage
        $fileExt = ($format === 'pbf') ? '.pbf' : '.mvt';
        // Mapbox vector tiles haben application/x-protobuf als Content-Type
        $contentType = 'application/x-protobuf';
    } elseif ($isMapboxStyle) {
        $fileExt = '.pbf';
        $contentType = 'application/x-protobuf';
    } elseif ($isMapboxGlyphs) {
        $fileExt = '.pbf';
        $contentType = 'application/x-protobuf';
    } else {
        $fileExt = '.png';
        $contentType = 'image/png';
    }
    
    // Include style/tileset info in filename for mapbox to avoid conflicts
    $filePrefix = '';
    if ($isMapboxStyle) {
        $styleId = rex_escape(rex_get('style_id', 'string', 'default'));
        $filePrefix = $styleId . '_';
    } elseif ($isMapboxV4) {
        $tileset = rex_escape(rex_get('tileset', 'string', 'default'));
        $filePrefix = str_replace(['mapbox.', ','], ['', '_'], $tileset) . '_';
    } elseif ($isVectorTiles) {
        $provider = rex_escape(rex_get('provider', 'string', 'default'));
        $filePrefix = 'vector_' . $provider . '_';
    } elseif ($isMapboxGlyphs) {
        // Glyphs: fontstack und range als Dateiname
        $fontstack = rex_escape(rex_get('fontstack', 'string', 'default'));
        $range = rex_escape(rex_get('range', 'string', '0-255'));
        $filePrefix = 'glyphs_' . str_replace([' ', ','], '_', $fontstack) . '_';
        $file = $dir . $filePrefix . $range . $fileExt;
    }
    
    if (!$isMapboxGlyphs) {
        $file = $dir . $filePrefix . "{$z}_{$x}_{$y}{$fileExt}";
    }
    
    // DEBUG: Test ob dieser Code ausgeführt wird
    file_put_contents($dir . 'debug_boot_called.txt', date('Y-m-d H:i:s') . ' - type=' . $type . ', file=' . $file . "\n", FILE_APPEND);
    
    if ((!is_file($file) || filemtime($file) < time() - ($ttl * 30)) && $type != '') {
        $server = array();
        switch ($type) {
            case "carto":
                $server[] = 'a.basemaps.cartocdn.com/rastertiles/voyager/';
                $server[] = 'b.basemaps.cartocdn.com/rastertiles/voyager/';
                $server[] = 'c.basemaps.cartocdn.com/rastertiles/voyager/';
                break;
            case "carto_light":
                $server[] = 'a.basemaps.cartocdn.com/rastertiles/light_all/';
                $server[] = 'b.basemaps.cartocdn.com/rastertiles/light_all/';
                $server[] = 'c.basemaps.cartocdn.com/rastertiles/light_all/';
                $server[] = 'd.basemaps.cartocdn.com/rastertiles/light_all/';
                break;
            case "carto_dark":
                $server[] = 'a.basemaps.cartocdn.com/rastertiles/dark_all/';
                $server[] = 'b.basemaps.cartocdn.com/rastertiles/dark_all/';
                $server[] = 'c.basemaps.cartocdn.com/rastertiles/dark_all/';
                $server[] = 'd.basemaps.cartocdn.com/rastertiles/dark_all/';
                break;           
               
            case "german":
                $server[] = 'a.tile.openstreetmap.de/';
                $server[] = 'b.tile.openstreetmap.de/';
                $server[] = 'c.tile.openstreetmap.de/';
                break;
            case "mapbox_style":
                // Mapbox Vector Tiles from Style - requires username, style_id and access_token
                $username = rex_escape(rex_get('username', 'string', ''));
                $styleId = rex_escape(rex_get('style_id', 'string', ''));
                $accessToken = rex_get('access_token', 'string', '');
                $tileSize = rex_get('tilesize', 'int', 512);
                
                if ($username && $styleId && $accessToken) {
                    // Mapbox Styles API provides vector tiles
                    $server[] = "api.mapbox.com/styles/v1/{$username}/{$styleId}/tiles/{$tileSize}/";
                }
                break;
            case "mapbox_v4":
                // Mapbox V4 API - requires tileset and access_token
                $tileset = rex_get('tileset', 'string', '');
                $accessToken = rex_get('access_token', 'string', '');
                $format = rex_get('format', 'string', 'mvt');
                
                if ($tileset && $accessToken) {
                    $server[] = "api.mapbox.com/v4/{$tileset}/";
                }
                break;
            case "mapbox_glyphs":
                // Mapbox Glyphs (Fonts) API
                $fontstack = rex_get('fontstack', 'string', '');
                $accessToken = rex_get('access_token', 'string', '');
                
                if ($fontstack && $accessToken) {
                    // Fontstack muss URL-encoded werden (z.B. "Open Sans Regular" -> "Open%20Sans%20Regular")
                    $fontstackEncoded = str_replace(' ', '%20', $fontstack);
                    $server[] = "api.mapbox.com/fonts/v1/mapbox/{$fontstackEncoded}/";
                }
                break;
            case "vector_tiles":
                // Generische Vector Tiles von verschiedenen Providern
                $provider = rex_get('provider', 'string', '');
                $apiKey = rex_get('api_key', 'string', '');
                
                switch($provider) {
                    case 'maptiler':
                        // Maptiler Vector Tiles - https://docs.maptiler.com/cloud/api/tiles/
                        if ($apiKey) {
                            $server[] = "api.maptiler.com/tiles/v3/";
                        }
                        break;
                    case 'openmaptiles':
                        // OpenMapTiles Demo Server (begrenzt)
                        $server[] = "demotiles.maplibre.org/tiles/";
                        break;
                    case 'protomaps':
                        // Protomaps (kostenlos via CDN)
                        $server[] = "api.protomaps.com/tiles/v3/";
                        break;
                    default:
                        error_log('OSMProxy: Unknown vector_tiles provider: ' . $provider);
                }
                break;
            default:
                $server[] = 'a.tile.openstreetmap.org/';
                $server[] = 'b.tile.openstreetmap.org/';
                $server[] = 'c.tile.openstreetmap.org/';
        }
        
        if (empty($server)) {
            die('Missing parameters');
        }
        
        $url = 'https://' . $server[array_rand($server)];
        
        if ($type === 'mapbox_style') {
            $accessToken = rex_get('access_token', 'string', '');
            $url .= $z . "/" . $x . "/" . $y . "?access_token=" . urlencode($accessToken);
        } elseif ($type === 'mapbox_v4') {
            $accessToken = rex_get('access_token', 'string', '');
            $format = rex_get('format', 'string', 'mvt');
            $url .= $z . "/" . $x . "/" . $y . "." . $format . "?access_token=" . urlencode($accessToken);
        } elseif ($type === 'mapbox_glyphs') {
            $accessToken = rex_get('access_token', 'string', '');
            $range = rex_get('range', 'string', '0-255');
            $url .= $range . ".pbf?access_token=" . urlencode($accessToken);
        } elseif ($type === 'vector_tiles') {
            $provider = rex_get('provider', 'string', '');
            $apiKey = rex_get('api_key', 'string', '');
            
            switch($provider) {
                case 'maptiler':
                    // Maptiler: /tiles/{z}/{x}/{y}.pbf?key=xxx
                    $url .= $z . "/" . $x . "/" . $y . ".pbf?key=" . urlencode($apiKey);
                    break;
                case 'openmaptiles':
                    // OpenMapTiles Demo: /{z}/{x}/{y}.pbf
                    $url .= $z . "/" . $x . "/" . $y . ".pbf";
                    break;
                case 'protomaps':
                    // Protomaps: /{z}/{x}/{y}.mvt
                    $url .= $z . "/" . $x . "/" . $y . ".mvt";
                    break;
            }
        } else {
            $url .= $z . "/" . $x . "/" . $y . ".png";
        }
        
        error_log('OSMProxy: Fetching from ' . $url);
        
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);  // Load into memory
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_ENCODING, "");  // Auto-decompress gzip/deflate
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);  // For local dev
        $data = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($data !== false && $httpCode == 200) {
            file_put_contents($file, $data);
            chmod($file, 0644);
            file_put_contents($dir . 'debug_saved.txt', date('Y-m-d H:i:s') . ' - Saved: ' . basename($file) . ' (' . strlen($data) . ' bytes)' . "\n", FILE_APPEND);
        } else {
            file_put_contents($dir . 'debug_failed.txt', date('Y-m-d H:i:s') . ' - FAILED: HTTP ' . $httpCode . ' for ' . $url . "\n", FILE_APPEND);
            die('Failed to fetch tile: HTTP ' . $httpCode);
        }
    }
    
    // Datei ausliefern wenn sie existiert
    if (is_file($file)) {
        $exp_gmt = gmdate("D, d M Y H:i:s", time() + $ttl * 60) . " GMT";
        $mod_gmt = gmdate("D, d M Y H:i:s", filemtime($file)) . " GMT";
        header("Expires: " . $exp_gmt);
        header("Last-Modified: " . $mod_gmt);
        header("Cache-Control: public, max-age=" . $ttl * 60);
        header('Content-Type: ' . $contentType);
        readfile($file);
    } else {
        file_put_contents($dir . 'debug_nofile.txt', date('Y-m-d H:i:s') . ' - File not found: ' . $file . "\n", FILE_APPEND);
        header('HTTP/1.0 404 Not Found');
        die('Tile not found');
    }
    die();
}
