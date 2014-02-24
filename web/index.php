<?php

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use TylerSommer\Nice\Application;

require __DIR__ . '/../vendor/autoload.php';

Symfony\Component\Debug\Debug::enable();

$app = new Application();

$app->setParameter('twig.template_dir', __DIR__ . '/../views');

$app->set('routes', function(FastRoute\RouteCollector $r) {
    $r->addRoute('GET', '/', function(Application $app, Request $request) {
            $mtime = new DateTime('@' . filemtime(__DIR__ . '/packages.json'));

            return new Response(
                $app->get('twig')->render('index.html.twig', array(
                        'updatedAt' => $mtime,
                        'name'      => 'Terramar Labs'
                    )));
        });
});

$app->run();