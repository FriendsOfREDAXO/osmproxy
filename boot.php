<?php

declare(strict_types=1);

use FriendsOfREDAXO\OsmProxy\Proxy;

$addon = rex_addon::get('osmproxy');
rex_dir::create($addon->getCachePath());

if (rex::isBackend() && 'osmproxy/demo' === rex_request('page', 'string', '')) {
    rex_view::addJsFile($addon->getAssetsUrl('js/demo.js'));
}

$assetUrl = rex_get('osmproxy_asset', 'string', '');
if ('' !== $assetUrl) {
    (new Proxy())->handleAsset($assetUrl);
}

if (!rex::isBackend()) {
    (new Proxy())->handle();
}
