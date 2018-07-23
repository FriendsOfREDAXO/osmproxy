# OSM proxy
This Addon delivers a OpenStreetMap Tile Proxy for REDAXO

Features: 

- delivers german tiels from openstreetmap.de
- or default tiles from openstreetmap.org
- stored files will be deleted afer 1 day
- does not accept direct calls of tiles


usage:

`/?osmtype=default&z=16&x=33973&y=21807`

or when using RewriteRule 

`/osmtype/german/16/33973/21807.png`

RewriteRule for Apache .htaccess
 
`RewriteRule ^osmtype/([^/]*)/([^/]*)/([^/]*)/([^/]*)\.png$ /?osmtype=$1&z=$2&x=$3&y=$4 [L]` 

How to use it in leaflet?

Example with RewriteRule

`var tiles = L.tileLayer('/osmtype/german/{z}/{x}/{y}', {`


### Credits

- [FriendsOfREDAXO](https://github.com/FriendsOfREDAXO)

**Projekt-Lead**

[Thomas Skerbis](https://github.com/skerbis)
