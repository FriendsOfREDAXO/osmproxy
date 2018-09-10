# OSM proxy
This Addon delivers a OpenStreetMap Tile Proxy for REDAXO

Features: 

- delivers german tiles from openstreetmap.de (type=german) 
- or default tiles from openstreetmap.org (type=default)
- stored files will be deleted afer 1 day
- does not accept direct calls of tiles from external sites

Types: 
- standard
- german
- wikipedia
- carto
- carto_light

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
