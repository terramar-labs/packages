<?php

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Terramar\Packages\Application;

require __DIR__ . '/../vendor/autoload.php';

Symfony\Component\Debug\Debug::enable();

$app = new Application('dev', true);
$app->run();
