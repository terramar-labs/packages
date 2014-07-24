<?php

namespace Terramar\Packages\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Reference;
use Terramar\Packages\Events;
use Terramar\Packages\Plugin\ContainerConfiguratorInterface;
use Terramar\Packages\Plugin\PluginInterface;

class PackagesExtension extends Extension
{
    /**
     * @var array
     */
    private $options = array();

    /**
     * @var array|PluginInterface[]
     */
    private $plugins = array();

    /**
     * Constructor
     *
     * @param array $options
     */
    public function __construct(array $plugins = array(), array $options = array())
    {
        $this->plugins = $plugins;
        $this->options = $options;
    }
    
    /**
     * Returns extension configuration
     *
     * @param array            $config    An array of configuration values
     * @param ContainerBuilder $container A ContainerBuilder instance
     *
     * @return PackagesConfiguration
     */
    public function getConfiguration(array $config, ContainerBuilder $container)
    {
        return new PackagesConfiguration();
    }
    
    /**
     * Loads a specific configuration.
     *
     * @param array            $configs    An array of configuration values
     * @param ContainerBuilder $container A ContainerBuilder instance
     *
     * @throws \InvalidArgumentException When provided tag is not defined in this extension
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configs[] = $this->options;
        $configuration = $this->getConfiguration($configs, $container);
        $config = $this->processConfiguration($configuration, $configs);

        $container->register('router.collector', 'Terramar\Packages\Router\RouteCollector')
            ->addArgument(new Reference('router.parser'))
            ->addArgument(new Reference('router.data_generator'));
        
        $container->register('packages.helper.sync', 'Terramar\Packages\Helper\SyncHelper')
            ->addArgument(new Reference('event_dispatcher'));
        
        $container->setParameter('packages.configuration', array(
                'output_dir' => $config['output_dir']
            ));
        
        $container->register('packages.helper.resque', 'Terramar\Packages\Helper\ResqueHelper');
        
        $container->setParameter('packages.resque.host', $config['resque']['host']);
        $container->setParameter('packages.resque.port', $config['resque']['port']);
        $container->setParameter('packages.resque.database', $config['resque']['database']);
        
        $container->register('packages.helper.plugin', 'Terramar\Packages\Helper\PluginHelper');

        $plugins = array();
        foreach ($this->plugins as $plugin) {
            $plugin->configure($container);
            $plugins[] = $plugin->getName();
        }
        
        $container->setParameter('packages.registered_plugins', $plugins);
    }
}