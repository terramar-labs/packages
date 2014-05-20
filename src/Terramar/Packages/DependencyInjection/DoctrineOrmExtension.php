<?php

namespace Terramar\Packages\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Reference;

class DoctrineOrmExtension extends Extension
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
     * @return DoctrineOrmConfiguration
     */
    public function getConfiguration(array $config, ContainerBuilder $container)
    {
        return new DoctrineOrmConfiguration();
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

        $container->register('doctrine.orm.metadata.annotation', 'Doctrine\ORM\Mapping\Driver\AnnotationDriver')
            ->setFactoryService('doctrine.orm.configuration')
            ->setFactoryMethod('newDefaultAnnotationDriver')
            ->addArgument($config['mapping']['paths'])
            ->addArgument(false);

        $container->register('doctrine.orm.configuration', 'Doctrine\ORM\Configuration')
            ->addMethodCall('setMetadataDriverImpl', array(new Reference('doctrine.orm.metadata.annotation')))
            ->addMethodCall('setProxyNamespace', array('Proxy'));
        
        if ($container->getParameter('app.cache') === true) {
            $container->getDefinition('doctrine.orm.configuration')
                ->addMethodCall('setProxyDir', array('%app.cache_dir%/doctrine'));
        }

        $container->register('doctrine.orm.entity_manager', 'Doctrine\ORM\EntityManager')
            ->setFactoryClass('Doctrine\ORM\EntityManager')
            ->setFactoryMethod('create')
            ->addArgument($config['database'])
            ->addArgument(new Reference('doctrine.orm.configuration'));
    }
}