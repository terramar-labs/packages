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

        $this->addNamedRoute('manage_configurations', 'GET', '/manage/configurations', 'Terramar\Packages\Controller\ConfigurationController::indexAction');
        $this->addNamedRoute('manage_configuration_new', 'GET', '/manage/configuration/new', 'Terramar\Packages\Controller\ConfigurationController::newAction');
        $this->addNamedRoute('manage_configuration_create', 'POST', '/manage/configuration/create', 'Terramar\Packages\Controller\ConfigurationController::createAction');
        $this->addNamedRoute('manage_configuration_edit', 'GET', '/manage/configuration/{id}/edit', 'Terramar\Packages\Controller\ConfigurationController::editAction');
        $this->addNamedRoute('manage_configuration_update', 'POST', '/manage/configuration/{id}/update', 'Terramar\Packages\Controller\ConfigurationController::updateAction');
        $this->addNamedRoute('manage_configuration_sync', 'GET', '/manage/configuration/{id}/sync', 'Terramar\Packages\Controller\ConfigurationController::syncAction');
    }
}
