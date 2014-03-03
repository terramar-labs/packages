<?php

require __DIR__ . '/../vendor/autoload.php';

$app = new Terramar\Packages\Application('prod', false);
$app->run();
