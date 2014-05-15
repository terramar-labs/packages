<?php

use Doctrine\ORM\Tools\Console\ConsoleRunner;

require_once __DIR__ . '/vendor/autoload.php';

Symfony\Component\Debug\Debug::enable();

$app = new Terramar\Packages\Application('dev', true);
$app->boot();

// replace with mechanism to retrieve EntityManager in your app
$entityManager = $app->get('doctrine.orm.entity_manager');

return ConsoleRunner::createHelperSet($entityManager);