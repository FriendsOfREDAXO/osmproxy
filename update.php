<?php
$addon = rex_addon::get('osmproxy');

if (rex_string::versionCompare($addon->getVersion(), '1.5.0', '<')) {
    rex_dir::delete($addon->getDataPath());
    rex_dir::create($addon->getCachePath());
}
