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
        $this->addNamedRoute('logout', 'GET', '/logout', '');

        $this->addNamedRoute('manage', 'GET', '/manage', 'Terramar\Packages\Controller\ManageController::indexAction');

        $this->addNamedRoute('manage_packages', 'GET', '/manage/packages', 'Terramar\Packages\Controller\PackageController::indexAction');
        $this->addNamedRoute('manage_package_new', 'GET', '/manage/package/new', 'Terramar\Packages\Controller\PackageController::newAction');
        $this->addNamedRoute('manage_package_create', 'POST', '/manage/package/create', 'Terramar\Packages\Controller\PackageController::createAction');
        $this->addNamedRoute('manage_package_edit', 'GET', '/manage/package/{id}/edit', 'Terramar\Packages\Controller\PackageController::editAction');
        $this->addNamedRoute('manage_package_update', 'POST', '/manage/package/{id}/update', 'Terramar\Packages\Controller\PackageController::updateAction');
    }
}
