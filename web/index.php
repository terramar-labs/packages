<?php

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Nice\Application;

require __DIR__ . '/../vendor/autoload.php';

Symfony\Component\Debug\Debug::enable();

$app = new Application('dev', true);

$app->set('routes', function(FastRoute\RouteCollector $r) {
    $r->addRoute('GET', '/', function(Application $app, Request $request) {
            $mtime = new DateTime('@' . filemtime(__DIR__ . '/packages.json'));

            $config = \Symfony\Component\Yaml\Yaml::parse(file_get_contents(__DIR__ . '/../config.yml'));
            
            return new Response(
                $app->get('twig')->render('index.html.twig', array(
                        'updatedAt' => $mtime,
                        'name'      => $config['satis']['name']
                    )));
        });
});

$app->run();