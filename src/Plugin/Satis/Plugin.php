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
use Terramar\Packages\Plugin\RouterPluginInterface;
use Terramar\Packages\Router\RouteCollector;

class Plugin implements PluginInterface, RouterPluginInterface
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

        $container->register('packages.plugin.satis.config_helper',
            'Terramar\Packages\Plugin\Satis\ConfigurationHelper')
            ->addArgument(new Reference('doctrine.orm.entity_manager'))
            ->addArgument(new Reference('router.url_generator'))
            ->addArgument('%app.root_dir%')
            ->addArgument('%app.cache_dir%')
            ->addArgument('%packages.configuration%');

        $container->register('packages.plugin.satis.frontend_controller', 'Terramar\Packages\Plugin\Satis\FrontendController')
            ->addArgument('%packages.configuration%')
            ->addArgument(new Reference('security.authenticator'));

        $container->getDefinition('packages.controller_manager')
            ->addMethodCall('registerController',
                [Actions::PACKAGE_EDIT, 'Terramar\Packages\Plugin\Satis\Controller::editAction'])
            ->addMethodCall('registerController',
                [Actions::PACKAGE_UPDATE, 'Terramar\Packages\Plugin\Satis\Controller::updateAction']);

        $container->getDefinition('packages.command_registry')
            ->addMethodCall('addCommand', ['Terramar\Packages\Plugin\Satis\Command\BuildCommand'])
            ->addMethodCall('addCommand', ['Terramar\Packages\Plugin\Satis\Command\UpdateCommand']);
    }

    /**
     * Configure the given RouteCollector.
     *
     * This method allows a plugin to register additional HTTP routes with the
     * RouteCollector.
     *
     * @param RouteCollector $collector
     * @return void
     */
    public function collect(RouteCollector $collector)
    {
        $collector->map('/packages.json', 'satis_packages', 'packages.plugin.satis.frontend_controller:outputAction');
        $collector->map('/include/{file}', null, 'packages.plugin.satis.frontend_controller:outputAction');
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
