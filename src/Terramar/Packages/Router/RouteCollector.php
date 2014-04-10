<?php

namespace Terramar\Packages\Router;

use Nice\Router\RouteCollector as BaseCollector;

class RouteCollector extends BaseCollector
{
    /**
     * Perform any collection
     *
     * @return void
     */
    protected function collectRoutes()
    {
        $this->addNamedRoute('home', 'GET', '/', 'Terramar\Packages\Controller\DefaultController::indexAction');
        $this->addNamedRoute('login', 'GET', '/login', 'Terramar\Packages\Controller\DefaultController::loginAction');

        $this->addNamedRoute('manage', 'GET', '/manage', 'Terramar\Packages\Controller\ManageController::indexAction');

        $this->addNamedRoute('manage_packages', 'GET', '/packages', 'Terramar\Packages\Controller\PackageController::indexAction');
        $this->addNamedRoute('manage_package_new', 'GET', '/package/new', 'Terramar\Packages\Controller\PackageController::newAction');
        $this->addNamedRoute('manage_package_create', 'POST', '/package/create', 'Terramar\Packages\Controller\PackageController::createAction');
    }
}
