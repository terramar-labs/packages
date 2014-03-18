<?php

namespace Terramar\Packages;

use Nice\Application as BaseApplication;
use Nice\Extension\TwigExtension;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class Application extends BaseApplication
{
    /**
     * Loads the container configuration
     *
     * @param LoaderInterface $loader A LoaderInterface instance
     *
     * @return void
     */
    protected function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load(function (ContainerBuilder $container) {
                $container->register('router.dispatcher_factory', 'Terramar\Packages\Router\DispatcherFactory')
                    ->setArguments(array(
                            new Reference('router.collector'),
                            $this->getCacheDir() . '/Routes.php',
                            $this->debug
                        ));
            });
    }

    /**
     * Register default extensions
     */
    protected function registerDefaultExtensions()
    {
        parent::registerDefaultExtensions();

        $this->registerExtension(new TwigExtension($this->getRootDir() . '/views'));
    }
}
