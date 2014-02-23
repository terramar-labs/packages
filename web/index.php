<?php

require __DIR__ . '/../vendor/autoload.php';

Symfony\Component\Debug\Debug::enable();

$routeFactory = function(FastRoute\RouteCollector $r) {
    $r->addRoute('GET', '/', function(\Symfony\Component\HttpFoundation\Request $request) {
            $loader = new \Twig_Loader_Filesystem(__DIR__ . '/../views');
            $twig = new \Twig_Environment($loader);
            
            $mtime = new DateTime('@' . filemtime(__DIR__ . '/packages.json'));

            return new \Symfony\Component\HttpFoundation\Response(
                $twig->render('index.html.twig', array(
                    'updatedAt' => $mtime,
                    'name'      => 'Terramar Labs'
                )));
        });
};

$app = new \TylerSommer\Nice\Application($routeFactory);
$app->run();