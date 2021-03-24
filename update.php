<?php
$addon = rex_addon::get('osmproxy');
rex_dir::create($addon->getCachePath());
if (rex_string::versionCompare($addon->getVersion(), '1.4.1', '<')) {
    rex_dir::delete($addon->getDataPath());

}
