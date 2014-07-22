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

        $this->addNamedRoute('webhook_receive', 'POST', '/webhook/{id}/receive', 'Terramar\Packages\Controller\WebHookController::receiveAction');
        
        $this->addNamedRoute('manage', 'GET', '/manage', 'Terramar\Packages\Controller\ManageController::indexAction');

        $this->addNamedRoute('manage_packages', 'GET', '/manage/packages', 'Terramar\Packages\Controller\PackageController::indexAction');
        $this->addNamedRoute('manage_package_edit', 'GET', '/manage/package/{id}/edit', 'Terramar\Packages\Controller\PackageController::editAction');
        $this->addNamedRoute('manage_package_update', 'POST', '/manage/package/{id}/update', 'Terramar\Packages\Controller\PackageController::updateAction');
        $this->addNamedRoute('manage_package_toggle', 'GET', '/manage/package/{id}/toggle', 'Terramar\Packages\Controller\PackageController::toggleAction');

        $this->addNamedRoute('manage_remotes', 'GET', '/manage/remotes', 'Terramar\Packages\Controller\RemoteController::indexAction');
        $this->addNamedRoute('manage_remote_new', 'GET', '/manage/remote/new', 'Terramar\Packages\Controller\RemoteController::newAction');
        $this->addNamedRoute('manage_remote_create', 'POST', '/manage/remote/create', 'Terramar\Packages\Controller\RemoteController::createAction');
        $this->addNamedRoute('manage_remote_edit', 'GET', '/manage/remote/{id}/edit', 'Terramar\Packages\Controller\RemoteController::editAction');
        $this->addNamedRoute('manage_remote_update', 'POST', '/manage/remote/{id}/update', 'Terramar\Packages\Controller\RemoteController::updateAction');
        $this->addNamedRoute('manage_remote_sync', 'GET', '/manage/remote/{id}/sync', 'Terramar\Packages\Controller\RemoteController::syncAction');
    }
}
