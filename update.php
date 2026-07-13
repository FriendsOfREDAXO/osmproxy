<?php

declare(strict_types=1);

$addon = rex_addon::get('osmproxy');
rex_dir::delete($addon->getCachePath());
rex_dir::create($addon->getCachePath());
