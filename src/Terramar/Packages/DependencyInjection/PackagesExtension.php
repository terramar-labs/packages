<?php

namespace Terramar\Packages\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Reference;

class PackagesExtension extends Extension
{
    /**
     * @var array
     */
    private $options = array();

    /**
     * Constructor
     *
     * @param array $options
     */
    public function __construct(array $options = array())
    {
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
            ->addArgument(new Reference('doctrine.orm.entity_manager'));
        
        $container->setParameter('packages.configuration', array(
                'name' => $config['name'],
                'homepage' => $config['homepage'],
                'output_dir' => $config['output_dir']
            ));
    }
}