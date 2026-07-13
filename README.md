# OSM Proxy

OSM Proxy ist das klassische REDAXO-Addon für einen serverseitigen Tile- und Asset-Proxy mit Cache. Es hält Raster-Tiles lokal vor, blockiert direkte Fremdaufrufe und hilft dabei, OSM-Kacheln und ausgewählte CDN-Assets datenschutzfreundlicher einzubinden.

## Status

- Version: 3.0.0
- Ziel: schlanker Proxy für klassische Kartenquellen und CDN-Assets
- Empfehlung für neue Projekte: Geolocation und vector_maps

## Was das AddOn aktuell bietet

- Raster-Tiles per Proxy und lokalem Cache
- freigegebene CDN-Assets über den gleichen Proxy-Mechanismus
- freie, sofort nutzbare Kartenquellen für OSM, Topo- und Basisstile
- freie Vektor-Styles über OpenFreeMap
- zentrale Provider-Liste im Backend
- kleine Demo-Seite mit Raster- und Vector-Beispielen
- PJAX-sichere Initialisierung der Demo über `rex:ready`

## Verfügbare Provider

### Raster

- OpenStreetMap
- OpenStreetMap Deutschland
- OpenTopoMap
- Wikimedia Maps
- CARTO Light
- CARTO Dark
- CARTO Voyager

### Vektor

- OpenFreeMap Liberty
- OpenFreeMap Bright
- OpenFreeMap Positron

OpenFreeMap ist eine frei nutzbare Vektor-Quelle ohne API-Key. Die Styles basieren auf offenen OpenStreetMap-Daten und eignen sich gut als moderne Basiskarte für MapLibre-basierte Frontends.

## Demo

Im Backend gibt es unter **Demo** eine kleine Übersicht der verfügbaren Provider. Dort findest du:

- eine klassische Raster-Proxy-URL
- eine freie Vektor-Style-URL
- einen Überblick aller im AddOn hinterlegten Provider
- Hinweise zur Attribution

Die Vektor-Demo lädt MapLibre nicht direkt aus dem Browser, sondern über den OSMProxy-Asset-Endpunkt. Damit laufen auch erlaubte CDN-Dateien durch den lokalen Proxy.

Wichtig: Der Proxy lädt einzelne freigegebene Dateien. Inhalte, die in CSS oder JSON weitere URLs nachladen, werden nicht automatisch umgeschrieben. Für solche Fälle müssen die eingebetteten URLs separat proxied oder als lokale Assets bereitgestellt werden.

## Empfehlung für neue Projekte

Für neue Vorhaben sind heute meist diese AddOns die bessere Wahl:

- **Geolocation** für Kartenverwaltung, Proxy, Geocoding und Leaflet-basierte Workflows
- **vector_maps** für moderne Vektorkarten mit Web Component und aktuellen Stil-Varianten

OSM Proxy bleibt damit vor allem als schlanke Kompatibilitäts- und Proxy-Lösung interessant.

## Proxied Assets

Erlaubte CDN-Quellen im aktuellen Stand:

- `unpkg.com`
- `cdn.jsdelivr.net`
- `tiles.openfreemap.org`

Typische Dateitypen, die darüber ausgeliefert werden können:

- CSS
- JavaScript
- JSON und Style-JSON
- SVG
- WOFF und WOFF2

## CDN-Dateien laden

Wenn du eine freigegebene CDN-Datei über den Proxy laden willst, verwendest du den Asset-Endpunkt mit der gewünschten Original-URL als Parameter:

```text
/redaxo/index.php?osmproxy_asset=https://unpkg.com/maplibre-gl/dist/maplibre-gl.js
```

Für CSS funktioniert das genauso:

```text
/redaxo/index.php?osmproxy_asset=https://unpkg.com/maplibre-gl/dist/maplibre-gl.css
```

Der Proxy holt die Datei vom erlaubten Host, speichert sie im Cache und liefert sie dann unter deiner REDAXO-Domain aus. Damit kannst du im Frontend oder in der Demo statt der Original-CDN-URL die proxied URL einbinden.

Wichtig:

- erlaubt sind nur die Hosts aus der Whitelist im AddOn
- der Proxy lädt immer nur genau eine Datei
- eingebettete Nachlade-URLs in CSS oder JSON werden nicht automatisch umgeschrieben

Wenn ein CSS- oder JSON-Asset weitere Dateien referenziert, musst du diese zusätzlichen URLs ebenfalls über den Proxy bereitstellen oder als lokale Assets einbinden.

## Grenzen

Der Proxy ist bewusst einfach gehalten. Er proxied einzelne URLs, aber er rewritet keine eingebetteten Nachlade-URLs innerhalb von CSS, JS oder JSON automatisch. Für vollwertige Offline- oder Same-Origin-Setups braucht es entweder zusätzliche Proxy-Regeln oder lokale Assets.

## Nutzung

Raster-Proxy-Beispiel:

```text
/?osmtype=opentopomap&z=12&x=2208&y=1362
```

Apache-Rewrite:

```apache
RewriteRule ^osmtype/([^/]*)/([^/]*)/([^/]*)/([^/]*)\.png$ /?osmtype=$1&z=$2&x=$3&y=$4 [L]
```

Nginx-Rewrite:

```nginx
rewrite ^/osmtype/([^/]*)/([^/]*)/([^/]*)/([^/]*)\.png$ /?osmtype=$1&z=$2&x=$3&y=$4 last;
```

Beispiel für Leaflet:

```javascript
var tiles = L.tileLayer('/osmtype/opentopomap/{z}/{x}/{y}.png', {
	attribution: 'Kartendaten © OpenStreetMap contributors, SRTM | Kartendarstellung © OpenTopoMap (CC-BY-SA)'
});
```

Vektor-Style-Beispiel:

```javascript
const map = new maplibregl.Map({
	container: 'map',
	style: 'https://tiles.openfreemap.org/styles/liberty',
	center: [13.388, 52.517],
	zoom: 9.5,
});
```

## Hinweis zu Lizenzen

Bei freien Kartenquellen ist die korrekte Attribution Pflicht. Bitte beachte immer die Hinweise des jeweiligen Providers. Für OpenFreeMap wird die Attribution auf der Website selbst dokumentiert und sollte im Frontend sichtbar sein.

## Changelog

Die Modernisierung hebt das AddOn auf eine neue 3.0.0-Linie mit:

- zentraler Provider-Verwaltung
- freier Vektor-Unterstützung
- Demo-Seite im Backend
- Proxied CDN-Assets für die Demo
- PJAX-sichere Initialisierung
- klare Doku zu Grenzen und empfohlenen Alternativen

## Credits

- Friends Of REDAXO
- Thomas Skerbis
