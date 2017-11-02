<?php

/*
 * Copyright (c) Terramar Labs
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Terramar\Packages\Plugin\GitHub;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Terramar\Packages\Plugin\Actions;
use Terramar\Packages\Plugin\PluginInterface;

class Plugin implements PluginInterface
{
    /**
     * Configure the given ContainerBuilder.
     *
     * This method allows a plugin to register additional services with the
     * service container.
     *
     * @param ContainerBuilder $container
     */
    public function configure(ContainerBuilder $container)
    {
        $container->register('packages.plugin.github.adapter', 'Terramar\Packages\Plugin\GitHub\SyncAdapter')
            ->addArgument(new Reference('doctrine.orm.entity_manager'))
            ->addArgument(new Reference('router.url_generator'));

        $container->getDefinition('packages.helper.sync')
            ->addMethodCall('registerAdapter', [new Reference('packages.plugin.github.adapter')]);

        $container->register('packages.plugin.github.package_subscriber',
            'Terramar\Packages\Plugin\GitHub\PackageSubscriber')
            ->addArgument(new Reference('packages.plugin.github.adapter'))
            ->addArgument(new Reference('doctrine.orm.entity_manager'))
            ->addTag('kernel.event_subscriber');

        $container->register('packages.plugin.github.remote_subscriber',
            'Terramar\Packages\Plugin\GitHub\RemoteSubscriber')
            ->addArgument(new Reference('packages.plugin.github.adapter'))
            ->addArgument(new Reference('doctrine.orm.entity_manager'))
            ->addTag('kernel.event_subscriber');

        $container->getDefinition('packages.controller_manager')
            ->addMethodCall('registerController',
                [Actions::REMOTE_NEW, 'Terramar\Packages\Plugin\GitHub\Controller::newAction'])
            ->addMethodCall('registerController',
                [Actions::REMOTE_CREATE, 'Terramar\Packages\Plugin\GitHub\Controller::createAction'])
            ->addMethodCall('registerController',
                [Actions::REMOTE_EDIT, 'Terramar\Packages\Plugin\GitHub\Controller::editAction'])
            ->addMethodCall('registerController',
                [Actions::REMOTE_UPDATE, 'Terramar\Packages\Plugin\GitHub\Controller::updateAction']);
    }

    /**
     * Get the plugin name.
     *
     * @return string
     */
    public function getName()
    {
        return 'GitHub';
    }

    /**
     */
    public function getVersion()
    {
        return;
    }
}
