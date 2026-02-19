# OSM proxy
This Addon delivers an OpenStreetMap tile proxy for REDAXO cms

Ensures the GDPR compliant use of OpenStreetmap tiles

The tile-cache will be stored inside the osmproxy cache folder `/redaxo/cache/addons/osmproxy/`.


## Features: 
- delivers raster tiles (PNG) from various OpenStreetMap providers
- delivers vector tiles (PBF/MVT) from Mapbox, Maptiler, and other providers
- supports custom map styles with MapLibre GL JS
- delivers map fonts/glyphs for vector tile rendering
- stored files will be deleted after 24 hours
- does not accept direct calls of tiles from external sites
- GDPR compliant tile caching

## Raster Tile Types (PNG): 
- `default` - OpenStreetMap.org standard tiles
- `german` - OpenStreetMap.de (German style)
- `carto` - CartoDB Voyager
- `carto_light` - CartoDB Light
- `carto_dark` - CartoDB Dark

## Vector Tile Types (PBF/MVT):
- `vector_tiles` - Generic vector tiles from multiple providers:
  - `maptiler` - Maptiler Vector Tiles (requires API key)
  - `openmaptiles` - OpenMapTiles Demo Server
  - `protomaps` - Protomaps CDN
- `mapbox_style` - Mapbox Styles API (requires Mapbox account)
- `mapbox_v4` - Mapbox V4 API (legacy, requires Mapbox account)
- `mapbox_glyphs` - Mapbox/Maptiler fonts for text rendering

> Please make sure to show the proper copyright attribution on the map, if needed. 
e.g.:

```html
<a href="https://carto.com/attribution">CARTO</a> for CARTO maps
<a href="https://wikimediafoundation.org/wiki/Maps_Terms_of_Use">Wikimedia maps</a> for Wikimedia
<a href="https://www.maptiler.com/copyright/">MapTiler</a> for Maptiler tiles
<a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors
```

---

## Usage Examples

### Raster Tiles (PNG) with Leaflet

Basic URL structure:

`/?osmtype=default&z=16&x=33973&y=21807`

or when using RewriteRule 

`/osmtype/german/16/33973/21807.png`

**Leaflet Example:**

```javascript
var tiles = L.tileLayer('/osmtype/german/{z}/{x}/{y}.png', {
    attribution: '© OpenStreetMap contributors'
});
```

### Vector Tiles (PBF/MVT) with MapLibre GL JS

**Maptiler Vector Tiles (recommended for GDPR compliance - EU servers):**

```javascript
const MAPTILER_API_KEY = 'your_api_key_here'; // Get free key from https://cloud.maptiler.com

const map = new maplibregl.Map({
    container: 'map',
    style: {
        "version": 8,
        "sources": {
            "maptiler": {
                "type": "vector",
                "tiles": [`${window.location.origin}/?osmtype=vector_tiles&provider=maptiler&api_key=${MAPTILER_API_KEY}&z={z}&x={x}&y={y}`]
            }
        },
        "glyphs": `https://api.maptiler.com/fonts/{fontstack}/{range}.pbf?key=${MAPTILER_API_KEY}`,
        "layers": [
            // Your custom style layers here
        ]
    },
    center: [6.68148, 51.19380],
    zoom: 13
});
```

**OpenMapTiles Demo (no API key needed, but limited):**

```javascript
const map = new maplibregl.Map({
    container: 'map',
    style: {
        "version": 8,
        "sources": {
            "openmaptiles": {
                "type": "vector",
                "tiles": [`${window.location.origin}/?osmtype=vector_tiles&provider=openmaptiles&z={z}&x={x}&y={y}`]
            }
        },
        "layers": [
            // Your custom style layers here
        ]
    }
});
```

### URL Parameters for Vector Tiles

**Maptiler Vector Tiles:**
```
/?osmtype=vector_tiles&provider=maptiler&api_key=YOUR_KEY&z={z}&x={x}&y={y}
```

**OpenMapTiles:**
```
/?osmtype=vector_tiles&provider=openmaptiles&z={z}&x={x}&y={y}
```

**Protomaps:**
```
/?osmtype=vector_tiles&provider=protomaps&z={z}&x={x}&y={y}
```

**Mapbox Glyphs/Fonts (cached):**
```
/?osmtype=mapbox_glyphs&fontstack=Open%20Sans%20Regular&range=0-255&access_token=YOUR_TOKEN
```

---

## RewriteRule Configuration

### Apache .htaccess
 
```apache
RewriteRule ^osmtype/([^/]*)/([^/]*)/([^/]*)/([^/]*)\.png$ /?osmtype=$1&z=$2&x=$3&y=$4 [L]
```

### nginx

```nginx
rewrite ^/osmtype/([^/]*)/([^/]*)/([^/]*)/([^/]*)\.png$ /?osmtype=$1&z=$2&x=$3&y=$4 last;
```

---

## Recommended Setup for GDPR Compliance

For maximum privacy compliance, use **Maptiler** (EU/Swiss servers) with **MapLibre GL JS**:

1. Get free API key from [Maptiler Cloud](https://cloud.maptiler.com) (100k tiles/month free)
2. Use `osmtype=vector_tiles&provider=maptiler` for tiles
3. All tiles are cached locally via OSMProxy
4. No external requests after first load (except fonts, which are also cacheable)
5. Full control over map styling and appearance

**Benefits of Vector Tiles:**
- Customizable styling (colors, fonts, layout)
- Better performance (smaller file sizes)
- Crisp rendering on high-DPI displays
- Complete design freedom
- GDPR-compliant with EU servers (Maptiler)

---

## Technical Details

**Caching:**
- All tiles (raster and vector) are cached locally in `/redaxo/cache/addons/osmproxy/`
- Cache lifetime: 24 hours (configurable via TTL)
- Automatic cleanup of expired tiles
- Gzip compression supported for vector tiles

**Security:**
- Referer check prevents external hotlinking (disabled for vector tiles due to JavaScript origin)
- Only whitelisted tile types are allowed
- API keys are validated server-side

**File Formats:**
- Raster tiles: `.png` (image/png)
- Vector tiles: `.pbf` or `.mvt` (application/x-protobuf)
- Fonts: `.pbf` (application/x-protobuf)

---

## Credits

- [FriendsOfREDAXO](https://github.com/FriendsOfREDAXO)

**Projekt-Lead**

[Thomas Skerbis](https://github.com/skerbis)

**Vector Tiles Extension**

Extended with Mapbox, Maptiler, and MapLibre GL JS support for custom vector tile styling and GDPR-compliant mapping solutions.
