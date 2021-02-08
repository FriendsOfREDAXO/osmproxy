<?php 
$addon = rex_addon::get('osmproxy');
rex_dir::create($addon->getCachePath());
