<?php

namespace Terramar\Packages\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Reference;

class DoctrineOrmExtension extends Extension
{
    /**
     * Loads a specific configuration.
     *
     * @param array            $config    An array of configuration values
     * @param ContainerBuilder $container A ContainerBuilder instance
     *
     * @throws \InvalidArgumentException When provided tag is not defined in this extension
     */
    public function load(array $config, ContainerBuilder $container)
    {
        $container->register('doctrine.orm.metadata.annotation', 'Doctrine\ORM\Mapping\Driver\AnnotationDriver')
            ->setFactoryService('doctrine.orm.configuration')
            ->setFactoryMethod('newDefaultAnnotationDriver')
            ->addArgument(array('%app.root_dir%/src/Terramar/Packages/Entity'))
            ->addArgument(false);

        $container->register('doctrine.orm.configuration', 'Doctrine\ORM\Configuration')
            ->addMethodCall('setMetadataDriverImpl', array(new Reference('doctrine.orm.metadata.annotation')))
            ->addMethodCall('setProxyDir', array('%app.cache_dir%'))
            ->addMethodCall('setProxyNamespace', array('Proxy'));

        $container->register('doctrine.orm.entity_manager', 'Doctrine\ORM\EntityManager')
            ->setFactoryClass('Doctrine\ORM\EntityManager')
            ->setFactoryMethod('create')
            ->addArgument(array(
                    'driver' => 'pdo_sqlite',
                    'path' => '%app.root_dir%/database.sqlite'
                ))
            ->addArgument(new Reference('doctrine.orm.configuration'));
    }
}