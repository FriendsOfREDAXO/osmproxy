<?php

declare(strict_types=1);

namespace FriendsOfREDAXO\OsmProxy;

use rex_addon;
use rex_dir;
use rex_file;
use rex_request;
use rex_response;

final class Proxy
{
    private const CACHE_TTL = 86400;
    private const DEFAULT_ASSET_HOSTS = [
        'unpkg.com',
        'cdn.jsdelivr.net',
        'tiles.openfreemap.org',
    ];

    public function handle(): void
    {
        $addon = rex_addon::get('osmproxy');
        rex_dir::create($addon->getCachePath());
        $this->deleteOSMCacheFiles($addon->getCachePath(), '*', self::CACHE_TTL);

        $type = rex_request('osmtype', 'string', '');
        if ('' === $type) {
            return;
        }

        rex_response::cleanOutputBuffers();

        $provider = Providers::get($type);
        if (null === $provider) {
            header('HTTP/1.1 404 Not Found');
            exit;
        }

        $x = rex_request('x', 'int', 0);
        $y = rex_request('y', 'int', 0);
        $z = rex_request('z', 'int', 0);

        if ($x < 0 || $y < 0 || $z < 0) {
            header('HTTP/1.1 400 Bad Request');
            exit;
        }

        if ('vector' === $provider['type']) {
            header('Location: ' . $provider['url']);
            exit;
        }

        $cacheFile = $addon->getCachePath() . $type . '_' . $z . '_' . $x . '_' . $y . '.png';
        $ttl = self::CACHE_TTL;

        if (!is_file($cacheFile) || filemtime($cacheFile) < time() - $ttl) {
            $url = $this->buildRasterUrl((string) $provider['url'], $x, $y, $z);
            $image = @file_get_contents($url);

            if (false === $image) {
                header('HTTP/1.1 502 Bad Gateway');
                exit;
            }

            rex_file::put($cacheFile, $image);
            @chmod($cacheFile, 0644);
        }

        header('Content-Type: image/png');
        header('Cache-Control: public, max-age=' . $ttl);
        header('Expires: ' . gmdate('D, d M Y H:i:s', time() + $ttl) . ' GMT');
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s', filemtime($cacheFile)) . ' GMT');

        readfile($cacheFile);
        exit;
    }

    public function handleAsset(string $url): void
    {
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            header('HTTP/1.1 400 Bad Request');
            exit;
        }

        $parts = parse_url($url);
        $host = is_array($parts) && isset($parts['host']) && is_string($parts['host']) ? $parts['host'] : '';

        if ('' === $host || !in_array($host, $this->getAllowedAssetHosts(), true)) {
            header('HTTP/1.1 403 Forbidden');
            exit;
        }

        $addon = rex_addon::get('osmproxy');
        rex_dir::create($addon->getCachePath() . 'assets/');

        $cacheKey = hash('sha256', $url);
        $extension = pathinfo((string) ($parts['path'] ?? ''), PATHINFO_EXTENSION);
        $cacheFile = $addon->getCachePath() . 'assets/' . $cacheKey . ($extension !== '' ? '.' . $extension : '.asset');

        if (!is_file($cacheFile) || filemtime($cacheFile) < time() - self::CACHE_TTL) {
            $content = @file_get_contents($url);

            if (false === $content) {
                header('HTTP/1.1 502 Bad Gateway');
                exit;
            }

            rex_file::put($cacheFile, $content);
            @chmod($cacheFile, 0644);
        }

        $mimeType = $this->resolveMimeType($extension);
        header('Content-Type: ' . $mimeType);
        header('Cache-Control: public, max-age=' . self::CACHE_TTL);
        header('Expires: ' . gmdate('D, d M Y H:i:s', time() + self::CACHE_TTL) . ' GMT');
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s', filemtime($cacheFile)) . ' GMT');

        readfile($cacheFile);
        exit;
    }

    private function buildRasterUrl(string $pattern, int $x, int $y, int $z): string
    {
        $hostPattern = $pattern;
        if (str_contains($pattern, '{a|b|c|d}')) {
            $hostPattern = str_replace('{a|b|c|d}', $this->randomHost(['a', 'b', 'c', 'd']), $pattern);
        } elseif (str_contains($pattern, '{a|b|c}')) {
            $hostPattern = str_replace('{a|b|c}', $this->randomHost(['a', 'b', 'c']), $pattern);
        }

        return str_replace(
            ['{z}', '{x}', '{y}'],
            [(string) $z, (string) $x, (string) $y],
            $hostPattern
        );
    }

    /**
     * @param array<int, string> $hosts
     */
    private function randomHost(array $hosts): string
    {
        return $hosts[array_rand($hosts)];
    }

    private function deleteOSMCacheFiles(string $dir, string $patterns = '*', int $timeout = self::CACHE_TTL): void
    {
        foreach (glob($dir . '*' . "{{$patterns}}", GLOB_BRACE) ?: [] as $file) {
            if (file_exists($file) && is_writable($file) && @filemtime($file) < (time() - $timeout)) {
                @unlink($file);
            }
        }
    }

    private function resolveMimeType(string $extension): string
    {
        return match (strtolower($extension)) {
            'css' => 'text/css; charset=utf-8',
            'js' => 'application/javascript; charset=utf-8',
            'json' => 'application/json; charset=utf-8',
            'map' => 'application/json; charset=utf-8',
            'svg' => 'image/svg+xml',
            'woff' => 'font/woff',
            'woff2' => 'font/woff2',
            default => 'application/octet-stream',
        };
    }

    /**
     * @return array<int, string>
     */
    private function getAllowedAssetHosts(): array
    {
        $addon = rex_addon::get('osmproxy');
        $configuredHosts = (string) $addon->getConfig('asset_hosts', '');
        $hosts = self::DEFAULT_ASSET_HOSTS;

        foreach (preg_split('/[\r\n,;]+/', $configuredHosts) ?: [] as $host) {
            $normalizedHost = strtolower(trim($host));
            if ('' === $normalizedHost) {
                continue;
            }

            if (!in_array($normalizedHost, $hosts, true)) {
                $hosts[] = $normalizedHost;
            }
        }

        return $hosts;
    }
}
