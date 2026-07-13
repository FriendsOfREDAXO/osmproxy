<?php

declare(strict_types=1);

$addon = rex_addon::get('osmproxy');
rex_dir::create($addon->getCachePath());
