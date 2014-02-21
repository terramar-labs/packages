<?php

require __DIR__ . '/../vendor/autoload.php';

Symfony\Component\Debug\Debug::enable();

$routeDispatcher = FastRoute\simpleDispatcher(function(FastRoute\RouteCollector $r) {
        $r->addRoute('POST', '/', 'handler1');
        $r->addRoute('PUT', '/', 'handler1');
        $r->addRoute('GET', '/test', 'handler2');
    });

$request = \Symfony\Component\HttpFoundation\Request::createFromGlobals();

$dispatcher = new \Symfony\Component\EventDispatcher\EventDispatcher();
$resolver = new \Symfony\Component\HttpKernel\Controller\ControllerResolver();
$kernel = new \Symfony\Component\HttpKernel\HttpKernel($dispatcher, $resolver);

$dispatcher->addListener('kernel.request', function(\Symfony\Component\HttpKernel\Event\GetResponseEvent $event) use ($routeDispatcher) {
        $request = $event->getRequest();
        $routeInfo = $routeDispatcher->dispatch($request->getMethod(), $request->getPathInfo());
        switch ($routeInfo[0]) {
            case FastRoute\Dispatcher::NOT_FOUND:
                $message = sprintf('No route found for "%s %s"', $request->getMethod(), $request->getPathInfo());

                throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException($message);

            case FastRoute\Dispatcher::METHOD_NOT_ALLOWED:
                $allowedMethods = $routeInfo[1];
                $message = sprintf('No route found for "%s %s": Method Not Allowed (Allow: %s)', $request->getMethod(), $request->getPathInfo(), implode(', ', $allowedMethods));

                throw new \Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException($allowedMethods, $message);

            case FastRoute\Dispatcher::FOUND:
                $handler = $routeInfo[1];
                $vars = $routeInfo[2];

                $request->attributes->set('_route_params', $vars);
                $request->attributes->set('_controller', $handler);
                
                break;
        }
    });

function handler1() {
    echo "handler1 got fired";
}

function handler2() {
    echo "handler2 got fired";
}

$response = $kernel->handle($request);
$response->send();

$kernel->terminate($request, $response);