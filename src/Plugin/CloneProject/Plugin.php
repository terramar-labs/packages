<?php

namespace Terramar\Packages\Plugin\CloneProject;

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
        $container->register('packages.plugin.clone_project.subscriber', 'Terramar\Packages\Plugin\CloneProject\EventSubscriber')
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
        $matches = array();
        preg_match('/version (\d\.\d\.\d(\.\d)?)/', exec('git --version'), $matches);
        
        return 'git-clone (' . $matches[1] . ')';
    }
}