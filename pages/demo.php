<?php

declare(strict_types=1);

use FriendsOfREDAXO\OsmProxy\Providers;

$addon = rex_addon::get('osmproxy');
$providers = Providers::grouped();

rex_view::addJsFile($addon->getAssetsUrl('js/demo.js'));

$maplibreJsUrl = rex_url::currentBackendPage([
    'osmproxy_asset' => 'https://unpkg.com/maplibre-gl/dist/maplibre-gl.js',
], false);

$maplibreCssUrl = rex_url::currentBackendPage([
    'osmproxy_asset' => 'https://unpkg.com/maplibre-gl/dist/maplibre-gl.css',
], false);

$mapStyleUrl = rex_url::currentBackendPage([
    'osmproxy_asset' => 'https://tiles.openfreemap.org/styles/liberty',
], false);

$rasterTileUrl = rtrim(rex::getServer(), '/') . '/' . ltrim((string) rex_url::frontendController([
    'osmtype' => 'opentopomap',
    'z' => 12,
    'x' => 2208,
    'y' => 1362,
], false), './');

$demoRaster = [
    'label' => 'OpenTopoMap',
    'type' => 'raster',
    'url' => 'https://{a|b|c}.tile.opentopomap.org/{z}/{x}/{y}.png',
    'attribution' => 'Kartendaten © OpenStreetMap contributors, SRTM | Kartendarstellung © OpenTopoMap (CC-BY-SA)',
];
$demoVector = [
    'label' => 'OpenFreeMap Liberty',
    'type' => 'vector',
    'url' => 'https://tiles.openfreemap.org/styles/liberty',
    'attribution' => 'OpenFreeMap © OpenMapTiles Data from OpenStreetMap',
];

?>
<style>
    .osmproxy-demo {
        display: grid;
        gap: 1.5rem;
    }

    .osmproxy-demo-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 1rem;
    }

    .osmproxy-demo-card {
        padding: 1rem;
        border: 1px solid var(--rex-border-color, #d6d6d6);
        border-radius: 12px;
        background: var(--rex-bg-color-100, #fff);
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.05);
    }


    .osmproxy-demo-debug {
        margin-top: 1rem;
        display: grid;
        gap: 0.75rem;
    }

    .osmproxy-demo-debug__row {
        display: grid;
        gap: 0.25rem;
        padding: 0.75rem 0.85rem;
        border-radius: 10px;
        background: rgba(0, 0, 0, 0.03);
        border: 1px solid rgba(0, 0, 0, 0.06);
    }

    .osmproxy-demo-debug__label {
        font-size: 0.78rem;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        color: rgba(31, 41, 55, 0.65);
    }

    .osmproxy-demo-debug__value {
        font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, 'Liberation Mono', monospace;
        font-size: 0.88rem;
        color: #1f2937;
        overflow-wrap: anywhere;
    }

    .osmproxy-demo-debug__status {
        font-weight: 700;
        color: #0f766e;
    }

    .osmproxy-demo-debug__status.is-error {
        color: #b91c1c;
    }
    .osmproxy-demo-box {
        min-height: 220px;
        border-radius: 12px;
        overflow: hidden;
        border: 1px solid var(--rex-border-color, #d6d6d6);
        position: relative;
    }

    .osmproxy-demo-box::before {
        content: 'Map preview';
        position: absolute;
        left: 1rem;
        top: 1rem;
        background: rgba(0, 0, 0, 0.7);
        color: #fff;
        border-radius: 999px;
        padding: 0.25rem 0.7rem;
        font-size: 0.8rem;
        z-index: 1;
    }

    .osmproxy-demo-box--raster {
        background-position: center center;
        background-size: cover;
    }

    .osmproxy-demo-raster-image {
        position: absolute;
        inset: 0;
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .osmproxy-demo-box--vector {
        background:
            linear-gradient(135deg, #d9ecfb 0%, #eff6fb 45%, #fdfdfd 100%);
    }

    .osmproxy-demo-preview {
        position: absolute;
        inset: 0;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        padding: 3rem 1rem 1rem;
    }

    .osmproxy-demo-preview__grid {
        position: absolute;
        inset: 0;
        background-image:
            linear-gradient(rgba(255, 255, 255, 0.2) 1px, transparent 1px),
            linear-gradient(90deg, rgba(255, 255, 255, 0.2) 1px, transparent 1px);
        background-size: 48px 48px;
        opacity: 0.35;
        pointer-events: none;
    }

    .osmproxy-demo-preview__svg {
        position: absolute;
        inset: 0;
        width: 100%;
        height: 100%;
    }

    .osmproxy-demo-map {
        position: absolute;
        inset: 0;
        width: 100%;
        height: 100%;
        min-height: 220px;
    }

    .osmproxy-demo-preview__marker {
        position: absolute;
        left: 52%;
        top: 46%;
        width: 18px;
        height: 18px;
        border-radius: 50%;
        background: #e23d3d;
        box-shadow: 0 0 0 6px rgba(226, 61, 61, 0.18);
        transform: translate(-50%, -50%);
    }

    .osmproxy-demo-preview__marker::after {
        content: '';
        position: absolute;
        left: 50%;
        top: 100%;
        width: 2px;
        height: 28px;
        background: rgba(226, 61, 61, 0.85);
        transform: translateX(-50%);
    }

    .osmproxy-demo-preview__legend {
        position: relative;
        z-index: 1;
        display: flex;
        justify-content: space-between;
        gap: 0.75rem;
        align-items: flex-end;
    }

    .osmproxy-demo-preview__title {
        font-size: 1.05rem;
        font-weight: 600;
        color: #1f2937;
        background: rgba(255, 255, 255, 0.84);
        border-radius: 0.75rem;
        padding: 0.35rem 0.65rem;
        backdrop-filter: blur(4px);
    }

    .osmproxy-demo-preview__meta {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        background: rgba(0, 0, 0, 0.68);
        color: #fff;
        border-radius: 999px;
        padding: 0.25rem 0.75rem;
        font-size: 0.78rem;
        line-height: 1.2;
    }

    .osmproxy-demo-preview__meta strong {
        font-weight: 700;
    }

    .osmproxy-demo-preview__label {
        position: relative;
        z-index: 1;
        display: flex;
        justify-content: flex-end;
    }

    .osmproxy-demo-preview__badge {
        display: inline-flex;
        gap: 0.35rem;
        align-items: center;
        background: rgba(255, 255, 255, 0.9);
        color: #223049;
        border-radius: 999px;
        padding: 0.35rem 0.8rem;
        font-size: 0.78rem;
        font-weight: 600;
        box-shadow: 0 6px 16px rgba(0, 0, 0, 0.12);
    }

    .osmproxy-demo-code {
        font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, 'Liberation Mono', monospace;
        font-size: 0.9rem;
        background: rgba(0, 0, 0, 0.04);
        border-radius: 8px;
        padding: 0.75rem;
        overflow: auto;
    }
</style>

<div class="osmproxy-demo">
    <div class="alert alert-info">
        Das AddOn stellt hier klassische Raster-Tiles über den REDAXO-Proxy bereit und listet zusätzlich moderne, freie Alternativen für Raster und Vektor.
        Für neue Projekte sind <strong>Geolocation</strong> und <strong>vector_maps</strong> in der Regel die bessere Wahl.
    </div>

    <div class="osmproxy-demo-grid">
        <div class="osmproxy-demo-card">
            <h3>Raster-Tiles</h3>
            <p>Beispiel für das bestehende OSMProxy-Verhalten.</p>
            <div class="osmproxy-demo-box osmproxy-demo-box--raster">
                <img class="osmproxy-demo-raster-image" src="<?= rex_escape($rasterTileUrl) ?>" alt="<?= rex_escape($demoRaster['label']) ?>">
                <div class="osmproxy-demo-preview">
                    <div class="osmproxy-demo-preview__legend">
                        <span class="osmproxy-demo-preview__title"><?= rex_escape($demoRaster['label']) ?></span>
                        <span class="osmproxy-demo-preview__meta"><strong>Raster</strong> via Proxy</span>
                    </div>
                    <div class="osmproxy-demo-preview__label">
                        <span class="osmproxy-demo-preview__badge">OpenTopoMap Sample Tile</span>
                    </div>
                </div>
            </div>
            <div class="osmproxy-demo-code">/?osmtype=opentopomap&amp;z=12&amp;x=2208&amp;y=1362</div>
            <div class="osmproxy-demo-debug">
                <div class="osmproxy-demo-debug__row">
                    <div class="osmproxy-demo-debug__label">Geladene Proxy-URL</div>
                    <div class="osmproxy-demo-debug__value" data-osmproxy-raster-url><?= rex_escape($rasterTileUrl) ?></div>
                </div>
                <div class="osmproxy-demo-debug__row">
                    <div class="osmproxy-demo-debug__label">Ladezustand</div>
                    <div class="osmproxy-demo-debug__value osmproxy-demo-debug__status" data-osmproxy-raster-status>wartet auf Bild</div>
                </div>
            </div>
        </div>

        <div class="osmproxy-demo-card">
            <h3>Vector-Tiles</h3>
            <p>Moderne Vektor-Basemap ohne eigenen API-Key als Beispiel.</p>
            <div class="osmproxy-demo-box osmproxy-demo-box--vector">
                <div
                    id="osmproxy-vector-map"
                    class="osmproxy-demo-map"
                    data-map-style="<?= rex_escape($mapStyleUrl) ?>"
                    data-map-lat="51.43"
                    data-map-lng="6.77"
                    data-map-zoom="13"
                    data-maplibre-js="<?= rex_escape($maplibreJsUrl) ?>"
                    data-maplibre-css="<?= rex_escape($maplibreCssUrl) ?>"
                ></div>
                <div class="osmproxy-demo-preview">
                    <div class="osmproxy-demo-preview__legend">
                        <span class="osmproxy-demo-preview__title"><?= rex_escape($demoVector['label']) ?></span>
                        <span class="osmproxy-demo-preview__meta"><strong>Vector</strong> Style</span>
                    </div>
                    <div class="osmproxy-demo-preview__label">
                        <span class="osmproxy-demo-preview__badge">MapLibre Style JSON</span>
                    </div>
                </div>
            </div>
            <div class="osmproxy-demo-code">https://tiles.openfreemap.org/styles/liberty</div>
            <div class="osmproxy-demo-debug">
                <div class="osmproxy-demo-debug__row">
                    <div class="osmproxy-demo-debug__label">Geladene Style-URL</div>
                    <div class="osmproxy-demo-debug__value" data-osmproxy-vector-style><?= rex_escape($mapStyleUrl) ?></div>
                </div>
                <div class="osmproxy-demo-debug__row">
                    <div class="osmproxy-demo-debug__label">Ladezustand</div>
                    <div class="osmproxy-demo-debug__value osmproxy-demo-debug__status" data-osmproxy-vector-status>wartet auf MapLibre</div>
                </div>
            </div>
        </div>
    </div>

    <div class="osmproxy-demo-card">
        <h3>Verfügbare Provider</h3>
        <div class="osmproxy-demo-grid">
            <?php foreach ($providers as $group => $items): ?>
                <section>
                    <h4><?= rex_escape(ucfirst((string) $group)) ?></h4>
                    <ul>
                        <?php foreach ($items as $provider): ?>
                            <li>
                                <strong><?= rex_escape($provider['label']) ?></strong><br>
                                <small><?= rex_escape($provider['type']) ?> · <?= rex_escape($provider['url']) ?></small>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </section>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="osmproxy-demo-card">
        <h3>Empfohlene moderne Alternativen</h3>
        <ul>
            <li><strong>Geolocation</strong> für Proxy, Kartenverwaltung, Geocoding und vollständige Karten-Workflows.</li>
            <li><strong>vector_maps</strong> für moderne Vektor-Karten mit Web Component und aktuellen Basemap-Stilen.</li>
        </ul>
    </div>

    <div class="osmproxy-demo-card">
        <h3>Hinweise zur Attribution</h3>
        <p>Bei der Nutzung freier Tile-Quellen müssen die jeweiligen Copyright- und Lizenzhinweise eingeblendet werden. Die Demo zeigt die wichtigsten Quellen bereits als Text.</p>
        <p><strong>OpenFreeMap</strong> nutzt offene Vektorkacheln und eignet sich besonders gut als moderne, freie Alternative ohne API-Key.</p>
    </div>
</div>
