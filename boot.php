<?php
if (rex_get('osmtype', 'string')) {

	if (parse_url($_SERVER['HTTP_REFERER'], PHP_URL_HOST) != $_SERVER['SERVER_NAME'] && !rex_backend_login::hasSession())
	{
		die();
	}
        $type = $dir = $file = $server = $url = $x = $y = $z = $ch = $fp = $exp_gmt = $mod_gmt = '';
	$type = rex_escape(rex_get('osmtype', 'string'));
	$dir = $this->getDataPath();
	foreach (glob($dir."*") as $file) {
		if(time() - filectime($file) > 86400){
			unlink($file);
		}
	}
	// Clear REDAXO OutputBuffers
	rex_response::cleanOutputBuffers();
	$ttl = 86400; 
	$x = rex_get('x', 'int');
	$y = rex_get('y', 'int');
	$z = rex_get('z', 'int');
	
	$file = $this->getDataPath()."/${z}_${x}_$y.png";
	
	if (!is_file($file) || filemtime($file)<time()-(86400*30))
	{
		$server = array();
		
		if ($type == 'carto')
		{
			$server[] = 'a.basemaps.cartocdn.com/rastertiles/voyager/';
			$server[] = 'b.basemaps.cartocdn.com/rastertiles/voyager/';
			$server[] = 'c.basemaps.cartocdn.com/rastertiles/voyager/';
			$server[] = 'd.basemaps.cartocdn.com/rastertiles/voyager/';
		}
		
		if ($type == 'carto_light')
		{
			$server[] = 'a.basemaps.cartocdn.com/rastertiles/light_all/';
			$server[] = 'b.basemaps.cartocdn.com/rastertiles/light_all/';
			$server[] = 'c.basemaps.cartocdn.com/rastertiles/light_all/';
			$server[] = 'd.basemaps.cartocdn.com/rastertiles/light_all/';
		}
					
		
		if ($type == 'german')
		{
			$server[] = 'a.tile.openstreetmap.de/tiles/osmde/';
			$server[] = 'b.tile.openstreetmap.de/tiles/osmde/';
			$server[] = 'c.tile.openstreetmap.de/tiles/osmde/';
		}

		else
		{
			$server[] = 'a.tile.openstreetmap.org/';
			$server[] = 'b.tile.openstreetmap.org/';
			$server[] = 'c.tile.openstreetmap.org/';
		}
		$url = 'https://'.$server[array_rand($server)];
		$url .= $z."/".$x."/".$y.".png";
		$ch = curl_init($url);
		$fp = fopen($file, "w");
		curl_setopt($ch, CURLOPT_FILE, $fp);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_exec($ch);
		curl_close($ch);
		fflush($fp);
		fclose($fp);
	}
	$exp_gmt = gmdate("D, d M Y H:i:s", time() + $ttl * 60) ." GMT";
	$mod_gmt = gmdate("D, d M Y H:i:s", filemtime($file)) ." GMT";
	header("Expires: " . $exp_gmt);
	header("Last-Modified: " . $mod_gmt);
	header("Cache-Control: public, max-age=" . $ttl * 60);
	header('Content-Type: image/png');
	readfile($file);
}

?>
