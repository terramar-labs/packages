<?php

namespace Terramar\Packages\Plugin\GitLab;

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
        $container->register('packages.plugin.gitlab.adapter', 'Terramar\Packages\Plugin\GitLab\SyncAdapter')
            ->addArgument(new Reference('doctrine.orm.entity_manager'))
            ->addArgument(new Reference('router.url_generator'));

        $container->getDefinition('packages.helper.sync')
            ->addMethodCall('registerAdapter', array(new Reference('packages.plugin.gitlab.adapter')));

        $container->register('packages.plugin.gitlab.subscriber', 'Terramar\Packages\Plugin\GitLab\PackageSubscriber')
            ->addArgument(new Reference('packages.plugin.gitlab.adapter'))
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
        return 'GitLab';
    }

    /**
     * @return null
     */
    public function getVersion()
    {
        return null;
    }
}
