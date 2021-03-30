# OSM proxy
This Addon delivers an OpenStreetMap Tile Proxy for REDAXO

Ensures the GDPR compliant use of OpenStreetmap tiles

The tile-cache will be stored inside the osmproxy cache folder `/redaxo/cache/addons/osmproxy/`.


## Features: 
- delivers carto tiles
- delivers wikimedia tiles 
- delivers german tiles from openstreetmap.de (type=default) 
- or default tiles from openstreetmap.org (type=default)
- stored files will be deleted afer 24 hours
- does not accept direct calls of tiles from external sites

## Types: 
- default (openstreetmap.org)
- german  (openstreetmap.de)
- wikipedia
- carto
- carto_light

> Please make sure to show the proper copyright attribution on the map, if needed. 
e.g.:

```html
<a href="https://carto.com/attribution">CARTO</a>` for CARTO maps and `<a href="https://wikimediafoundation.org/wiki/Maps_Terms_of_Use">Wikimedia maps</a>` for wikimedia.
```

Usage:

`/?osmtype=default&z=16&x=33973&y=21807`

or when using RewriteRule 

`/osmtype/german/16/33973/21807.png`

RewriteRule for Apache .htaccess
 
`RewriteRule ^osmtype/([^/]*)/([^/]*)/([^/]*)/([^/]*)\.png$ /?osmtype=$1&z=$2&x=$3&y=$4 [L]` 

nginx

`rewrite ^/osmtype/([^/]*)/([^/]*)/([^/]*)/([^/]*)\.png$ /?osmtype=$1&z=$2&x=$3&y=$4 last;`

How to use it in leaflet?

Example with RewriteRule

`var tiles = L.tileLayer('/osmtype/german/{z}/{x}/{y}.png', {`


## Credits

- [FriendsOfREDAXO](https://github.com/FriendsOfREDAXO)

**Projekt-Lead**

[Thomas Skerbis](https://github.com/skerbis)
