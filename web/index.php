<?php

/*
 * Copyright (c) Terramar Labs
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */
require __DIR__ . '/../vendor/autoload.php';

$app = new Terramar\Packages\Application('prod', false);
$app->run();
