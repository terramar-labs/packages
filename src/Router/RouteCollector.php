<?php

/*
 * Copyright (c) Terramar Labs
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

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
        $this->map('/', 'home', 'Terramar\Packages\Controller\DefaultController::indexAction');
        $this->map('/login', 'login', 'Terramar\Packages\Controller\DefaultController::loginAction');
        $this->map('/logout', 'logout', '');

        $this->map('/webhook/{id}/receive', 'webhook_receive', 'Terramar\Packages\Controller\WebHookController::receiveAction', ['POST']);
        
        $this->map('/manage', 'manage', 'Terramar\Packages\Controller\ManageController::indexAction');

        $this->map('/manage/packages', 'manage_packages', 'Terramar\Packages\Controller\PackageController::indexAction');
        $this->map('/manage/package/{id}/edit', 'manage_package_edit', 'Terramar\Packages\Controller\PackageController::editAction');
        $this->map('/manage/package/{id}/update', 'manage_package_update', 'Terramar\Packages\Controller\PackageController::updateAction', ['POST']);
        $this->map('/manage/package/{id}/toggle', 'manage_package_toggle', 'Terramar\Packages\Controller\PackageController::toggleAction');

        $this->map('/manage/remotes', 'manage_remotes', 'Terramar\Packages\Controller\RemoteController::indexAction');
        $this->map('/manage/remote/new', 'manage_remote_new', 'Terramar\Packages\Controller\RemoteController::newAction');
        $this->map('/manage/remote/create', 'manage_remote_create', 'Terramar\Packages\Controller\RemoteController::createAction', ['POST']);
        $this->map('/manage/remote/{id}/edit', 'manage_remote_edit', 'Terramar\Packages\Controller\RemoteController::editAction');
        $this->map('/manage/remote/{id}/update', 'manage_remote_update', 'Terramar\Packages\Controller\RemoteController::updateAction', ['POST']);
        $this->map('/manage/remote/{id}/sync', 'manage_remote_sync', 'Terramar\Packages\Controller\RemoteController::syncAction');
    }
}
