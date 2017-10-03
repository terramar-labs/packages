<?php

/*
 * Copyright (c) Terramar Labs
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Terramar\Packages\Plugin\Satis;

use Composer\Satis\Satis;
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
        $container->register('packages.plugin.satis.subscriber', 'Terramar\Packages\Plugin\Satis\EventSubscriber')
            ->addArgument(new Reference('packages.helper.resque'))
            ->addArgument(new Reference('doctrine.orm.entity_manager'))
            ->addTag('kernel.event_subscriber');

        $container->register('packages.plugin.satis.config_helper', 'Terramar\Packages\Plugin\Satis\ConfigurationHelper')
            ->addArgument(new Reference('doctrine.orm.entity_manager'))
            ->addArgument('%app.root_dir%')
            ->addArgument('%app.cache_dir%')
            ->addArgument('%packages.configuration%');

        $container->getDefinition('packages.controller_manager')
            ->addMethodCall('registerController', [
                Actions::PACKAGE_EDIT,
                'Terramar\Packages\Plugin\Satis\Controller::editAction',
            ])
            ->addMethodCall('registerController', [
                Actions::PACKAGE_UPDATE,
                'Terramar\Packages\Plugin\Satis\Controller::updateAction',
            ]);

        $container->getDefinition('packages.command_registry')
            ->addMethodCall('addCommand', ['Terramar\Packages\Plugin\Satis\Command\BuildCommand'])
            ->addMethodCall('addCommand', ['Terramar\Packages\Plugin\Satis\Command\UpdateCommand']);
    }

    /**
     * Get the plugin name.
     *
     * @return string
     */
    public function getName()
    {
        return 'Satis';
    }

    /**
     * @return string
     */
    public function getVersion()
    {
        return Satis::VERSION;
    }
}
