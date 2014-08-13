<?php

namespace Terramar\Packages\Plugin\Git;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Terramar\Packages\Plugin\Actions;
use Terramar\Packages\Plugin\PluginInterface;

class Plugin implements PluginInterface
{
    /**
     * @var string
     */
    private $version;

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
        $container->register('packages.plugin.git.adapter', 'Terramar\Packages\Plugin\Git\SyncAdapter')
            ->addArgument(new Reference('doctrine.orm.entity_manager'))
            ->addArgument(new Reference('router.url_generator'))
            ->addArgument(new Reference('packages.plugin.git.git'))
            ->addArgument('%app.cache_dir%');

        $container->getDefinition('packages.helper.sync')
            ->addMethodCall('registerAdapter', array(new Reference('packages.plugin.git.adapter')));
        
        $container->register('packages.plugin.git.subscriber', 'Terramar\Packages\Plugin\Git\EventSubscriber')
            ->addArgument(new Reference('doctrine.orm.entity_manager'))
            ->addTag('kernel.event_subscriber');
        
        $container->register('packages.plugin.git.git', 'PHPGit\Git');
        $container->addAliases(array(
            'git' => 'packages.plugin.git.git'
        ));
    }

    /**
     * Get the plugin name
     *
     * @return string
     */
    public function getName()
    {
        return 'git';
    }

    /**
     * @return string
     */
    public function getVersion()
    {
        if (!$this->version) {
            $matches = array();
            preg_match('/version (\d\.\d\.\d(\.\d)?)/', exec('git --version'), $matches);
            $this->version = $matches[1];
        }
        
        return $this->version;
    }
}