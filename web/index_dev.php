<?php

require __DIR__ . '/../vendor/autoload.php';

Symfony\Component\Debug\Debug::enable();

$app = new Terramar\Packages\Application('dev', true);
$app->run();
