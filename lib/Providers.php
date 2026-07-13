<?php

declare(strict_types=1);

namespace FriendsOfREDAXO\OsmProxy;

/**
 * Central provider catalog for raster and vector tile sources.
 */
final class Providers
{
    /**
     * Returns the available tile providers.
     *
     * @return array<int, array<string, mixed>>
     */
    public static function all(): array
    {
        return [
            'openstreetmap' => [
                'label' => 'OpenStreetMap',
                'type' => 'raster',
                'url' => 'https://tile.openstreetmap.org/{z}/{x}/{y}.png',
                'attribution' => 'Map data © OpenStreetMap contributors',
                'free' => true,
            ],
            'openstreetmap_de' => [
                'label' => 'OpenStreetMap Deutschland',
                'type' => 'raster',
                'url' => 'https://{a|b|c}.tile.openstreetmap.de/{z}/{x}/{y}.png',
                'attribution' => 'Map data © OpenStreetMap contributors',
                'free' => true,
            ],
            'opentopomap' => [
                'label' => 'OpenTopoMap',
                'type' => 'raster',
                'url' => 'https://{a|b|c}.tile.opentopomap.org/{z}/{x}/{y}.png',
                'attribution' => 'Kartendaten © OpenStreetMap contributors, SRTM | Kartendarstellung © OpenTopoMap (CC-BY-SA)',
                'free' => true,
            ],
            'wikimedia' => [
                'label' => 'Wikimedia Maps',
                'type' => 'raster',
                'url' => 'https://maps.wikimedia.org/osm-intl/{z}/{x}/{y}.png',
                'attribution' => 'Wikimedia maps | Map data © OpenStreetMap contributors',
                'free' => true,
            ],
            'carto_light' => [
                'label' => 'CARTO Light',
                'type' => 'raster',
                'url' => 'https://{a|b|c|d}.basemaps.cartocdn.com/rastertiles/light_all/{z}/{x}/{y}.png',
                'attribution' => '© OpenStreetMap contributors © CARTO',
                'free' => true,
            ],
            'carto_dark' => [
                'label' => 'CARTO Dark',
                'type' => 'raster',
                'url' => 'https://{a|b|c|d}.basemaps.cartocdn.com/rastertiles/dark_all/{z}/{x}/{y}.png',
                'attribution' => '© OpenStreetMap contributors © CARTO',
                'free' => true,
            ],
            'carto_voyager' => [
                'label' => 'CARTO Voyager',
                'type' => 'raster',
                'url' => 'https://{a|b|c}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}.png',
                'attribution' => '© OpenStreetMap contributors © CARTO',
                'free' => true,
            ],
            'openfreemap_liberty' => [
                'label' => 'OpenFreeMap Liberty',
                'type' => 'vector',
                'url' => 'https://tiles.openfreemap.org/styles/liberty',
                'attribution' => 'OpenFreeMap © OpenMapTiles Data from OpenStreetMap',
                'free' => true,
            ],
            'openfreemap_bright' => [
                'label' => 'OpenFreeMap Bright',
                'type' => 'vector',
                'url' => 'https://tiles.openfreemap.org/styles/bright',
                'attribution' => 'OpenFreeMap © OpenMapTiles Data from OpenStreetMap',
                'free' => true,
            ],
            'openfreemap_positron' => [
                'label' => 'OpenFreeMap Positron',
                'type' => 'vector',
                'url' => 'https://tiles.openfreemap.org/styles/positron',
                'attribution' => 'OpenFreeMap © OpenMapTiles Data from OpenStreetMap',
                'free' => true,
            ],
        ];
    }

    /**
     * Returns a provider by key.
     */
    public static function get(string $key): ?array
    {
        $providers = self::all();

        return $providers[$key] ?? null;
    }

    /**
     * Returns grouped provider suggestions for the demo.
     *
     * @return array<int, array<string, mixed>>
     */
    public static function grouped(): array
    {
        $providers = self::all();
        $groups = [
            'Raster' => [],
            'Vector' => [],
        ];

        foreach ($providers as $key => $provider) {
            $provider['key'] = $key;
            $groupName = 'vector' === $provider['type'] ? 'Vector' : 'Raster';
            $groups[$groupName][] = $provider;
        }

        return $groups;
    }
}
