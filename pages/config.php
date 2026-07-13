<?php

declare(strict_types=1);

$addon = rex_addon::get('osmproxy');
$func = rex_request('func', 'string', '');
$csrfToken = rex_csrf_token::factory('osmproxy_cache');

if ('clear_cache' === $func) {
    if (!$csrfToken->isValid()) {
        echo rex_view::error(rex_i18n::msg('csrf_token_invalid'));
    } else {
        if (rex_dir::delete($addon->getCachePath())) {
            rex_dir::create($addon->getCachePath());
            echo rex_view::success($addon->i18n('cache_cleared'));
        } else {
            echo rex_view::error($addon->i18n('cache_clear_error'));
        }
    }
}

$cacheDir = $addon->getCachePath();
$cacheBytes = 0;
$cacheFiles = 0;

if (is_dir($cacheDir)) {
    $iterator = new \RecursiveIteratorIterator(
        new \RecursiveDirectoryIterator($cacheDir, \FilesystemIterator::SKIP_DOTS)
    );

    foreach ($iterator as $file) {
        if ($file->isFile()) {
            $cacheBytes += (int) $file->getSize();
            ++$cacheFiles;
        }
    }
}

$cacheSizeHuman = match (true) {
    $cacheBytes >= 1_073_741_824 => number_format($cacheBytes / 1_073_741_824, 2, ',', '.') . ' GB',
    $cacheBytes >= 1_048_576 => number_format($cacheBytes / 1_048_576, 2, ',', '.') . ' MB',
    $cacheBytes >= 1_024 => number_format($cacheBytes / 1_024, 2, ',', '.') . ' KB',
    default => number_format($cacheBytes, 0, ',', '.') . ' Byte',
};

$form = rex_config_form::factory($addon->getName());
$form->addFieldset($addon->i18n('config'));

$providerField = $form->addSelectField('default_provider');
$providerField->setLabel($addon->i18n('default_provider'));
$providerField->setNotice($addon->i18n('default_provider_notice'));
$providerSelect = $providerField->getSelect();
$providerSelect->setSize(1);

foreach (array_keys(\FriendsOfREDAXO\OsmProxy\Providers::all()) as $providerKey) {
    $providerSelect->addOption($providerKey, $providerKey);
}

$referrerField = $form->addSelectField('allow_remote_referrer_check');
$referrerField->setLabel($addon->i18n('allow_remote_referrer_check'));
$referrerField->setNotice($addon->i18n('allow_remote_referrer_check_notice'));
$referrerSelect = $referrerField->getSelect();
$referrerSelect->setSize(1);
$referrerSelect->addOption($addon->i18n('yes'), '1');
$referrerSelect->addOption($addon->i18n('no'), '0');

$vectorField = $form->addSelectField('show_vector_examples');
$vectorField->setLabel($addon->i18n('show_vector_examples'));
$vectorField->setNotice($addon->i18n('show_vector_examples_notice'));
$vectorSelect = $vectorField->getSelect();
$vectorSelect->setSize(1);
$vectorSelect->addOption($addon->i18n('yes'), '1');
$vectorSelect->addOption($addon->i18n('no'), '0');

$assetHostsField = $form->addTextAreaField('asset_hosts');
$assetHostsField->setLabel($addon->i18n('asset_hosts'));
$assetHostsField->setNotice($addon->i18n('asset_hosts_notice'));
$assetHostsField->setAttribute('rows', '6');

$fragment = new rex_fragment();
$fragment->setVar('class', 'edit', false);
$fragment->setVar('title', $addon->i18n('config'), false);
$fragment->setVar('body', $form->get(), false);
echo $fragment->parse('core/page/section.php');

$cacheContent = '<p>' . $addon->i18n('cache_clear_notice') . '</p>';
$cacheContent .= '<table class="table table-condensed" style="max-width:420px;margin-bottom:16px">'
    . '<tr><th style="width:160px">' . rex_escape($addon->i18n('cache_files')) . '</th><td>' . number_format($cacheFiles, 0, ',', '.') . '</td></tr>'
    . '<tr><th>' . rex_escape($addon->i18n('cache_size')) . '</th><td><strong>' . rex_escape($cacheSizeHuman) . '</strong></td></tr>'
    . '</table>';
$cacheContent .= '<p><a class="btn btn-delete" href="' . rex_escape(rex_url::currentBackendPage(['func' => 'clear_cache'] + $csrfToken->getUrlParams())) . '" onclick="return confirm(\'' . rex_escape($addon->i18n('cache_clear_confirm')) . '\')">';
$cacheContent .= '<i class="rex-icon rex-icon-delete"></i> ' . rex_escape($addon->i18n('clear_cache'));
$cacheContent .= '</a></p>';

$cacheFragment = new rex_fragment();
$cacheFragment->setVar('class', 'edit', false);
$cacheFragment->setVar('title', $addon->i18n('cache'), false);
$cacheFragment->setVar('body', $cacheContent, false);
echo $cacheFragment->parse('core/page/section.php');
