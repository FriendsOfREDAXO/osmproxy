<?php
$addon = rex_addon::get('osmproxy');

if (rex_string::versionCompare($addon->getVersion(), '2.0.0', '<')) {
    rex_dir::delete($addon->getDataPath());
    rex_dir::create($addon->getCachePath());
}
