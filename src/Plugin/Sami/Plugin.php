<?php

namespace Terramar\Packages\Plugin\Sami;

use Sami\Sami;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Terramar\Packages\Plugin\PluginInterface;

class Plugin implements PluginInterface
{
    /**
     * Configure the given ContainerBuilder
     *
     * This method allows a plugin to register additional services with the
     * service container.
     *
     * @param ContainerBuilder $container
     *
     * @return void
     */
    public function configure(ContainerBuilder $container)
    {
        $container->register('packages.plugin.sami.subscriber', 'Terramar\Packages\Plugin\Sami\EventSubscriber')
            ->addArgument(new Reference('packages.helper.resque'))
            ->addArgument(new Reference('doctrine.orm.entity_manager'))
            ->addTag('kernel.event_subscriber');
    }

    /**
     * Get the plugin name
     *
     * @return string
     */
    public function getName()
    {
        return 'Sami';
    }

    /**
     * @return string
     */
    public function getVersion()
    {
        return Sami::VERSION;
    }
}