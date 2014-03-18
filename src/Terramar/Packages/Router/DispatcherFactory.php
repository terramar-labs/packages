<?php

namespace Terramar\Packages\Router;

use FastRoute\RouteCollector;
use Nice\Router\DispatcherFactory\CachedDispatcherFactory;

class DispatcherFactory extends CachedDispatcherFactory
{
    /**
     * Collect configured routes, actually doing the work
     *
     * Implement this method in a subclass
     *
     * @param RouteCollector $collector
     */
    protected function doCollectRoutes(RouteCollector $collector)
    {
        $collector->addRoute('GET', '/', 'Terramar\Packages\Controller\DefaultController::indexAction');
    }
}
