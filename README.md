# OSM proxy
This Addon delivers an OpenStreetMap Tile Proxy for REDAXO

Features: 

- delivers carto tiles
- delivers wikipedia tiles (from beta server) 
- delivers german tiles from openstreetmap.de (type=default) 
- or default tiles from openstreetmap.org (type=default)
- stored files will be deleted afer 1 day
- does not accept direct calls of tiles from external sites

Types: 
- default (openstreetmap.org)
- german  (openstreetmap.de)
- wikipedia
- carto
- carto_light

> Please make sure to show the proper copyright attribution on the map, if needed. 
e.g.: '<a href="https://carto.com/attribution">CARTO</a>' for CARTO maps and '<a href="https://wikimediafoundation.org/wiki/Maps_Terms_of_Use">Wikimedia maps</a>' for wikimedia.

Usage:

`/?osmtype=default&z=16&x=33973&y=21807`

or when using RewriteRule 

`/osmtype/german/16/33973/21807.png`

RewriteRule for Apache .htaccess
 
`RewriteRule ^osmtype/([^/]*)/([^/]*)/([^/]*)/([^/]*)\.png$ /?osmtype=$1&z=$2&x=$3&y=$4 [L]` 

How to use it in leaflet?

Example with RewriteRule

`var tiles = L.tileLayer('/osmtype/german/{z}/{x}/{y}.png', {`


### Credits

- [FriendsOfREDAXO](https://github.com/FriendsOfREDAXO)

**Projekt-Lead**

[Thomas Skerbis](https://github.com/skerbis)
