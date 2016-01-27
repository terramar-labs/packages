<?php

/*
 * Copyright (c) Terramar Labs
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Terramar\Packages\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Reference;
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
     * Constructor.
     *
     * @param array $options
     */
    public function __construct(array $plugins = array(), array $options = array())
    {
        $this->plugins = $plugins;
        $this->options = $options;
    }

    /**
     * Returns extension configuration.
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
     * @param array            $configs   An array of configuration values
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

        $container->register('packages.command_registry', 'Terramar\Packages\Console\CommandRegistry');

        $container->register('packages.helper.sync', 'Terramar\Packages\Helper\SyncHelper')
            ->addArgument(new Reference('event_dispatcher'));

        $container->setParameter('packages.configuration', array(
                'output_dir' => $config['output_dir'],
            ));

        $container->register('packages.helper.resque', 'Terramar\Packages\Helper\ResqueHelper');

        $container->setParameter('packages.resque.host', $config['resque']['host']);
        $container->setParameter('packages.resque.port', $config['resque']['port']);
        $container->setParameter('packages.resque.database', $config['resque']['database']);

        $container->register('packages.controller_manager', 'Terramar\Packages\Plugin\ControllerManager');

        $container->register('packages.fragment_handler.inline_renderer', 'Symfony\Component\HttpKernel\Fragment\InlineFragmentRenderer')
            ->addArgument(new Reference('http_kernel'))
            ->addArgument(new Reference('event_dispatcher'));
        $container->register('packages.fragment_handler', 'Symfony\Component\HttpKernel\Fragment\FragmentHandler')
            ->addArgument(array(
                    new Reference('packages.fragment_handler.inline_renderer'),
                ))
            ->addArgument(false)
            ->addMethodCall('setRequest', array(new Reference(
                    'request',
                    ContainerInterface::NULL_ON_INVALID_REFERENCE,
                    false
                )));

        $container->register('packages.twig_extension.plugin', 'Terramar\Packages\Twig\PluginControllerExtension')
            ->addArgument(new Reference('packages.controller_manager'))
            ->addArgument(new Reference('packages.fragment_handler'))
            ->addMethodCall('setRequest', array(new Reference(
                    'request',
                    ContainerInterface::NULL_ON_INVALID_REFERENCE,
                    false
                )))
            ->addTag('twig.extension');

        $container->register('packages.fragment_handler.uri_signer', 'Symfony\Component\HttpKernel\UriSigner')
            ->addArgument('');

        $container->register('packages.fragment_handler.listener', 'Symfony\Component\HttpKernel\EventListener\FragmentListener')
            ->addArgument(new Reference('packages.fragment_handler.uri_signer'))
            ->addTag('kernel.event_subscriber');

        $container->register('packages.helper.plugin', 'Terramar\Packages\Helper\PluginHelper')
            ->addArgument(new Reference('packages.controller_manager'))
            ->addArgument(new Reference('packages.fragment_handler'))
            ->addMethodCall('setRequest', array(new Reference(
                    'request',
                    ContainerInterface::NULL_ON_INVALID_REFERENCE,
                    false
                )));

        $plugins = array();
        foreach ($this->plugins as $plugin) {
            $plugin->configure($container);
            $plugins[$plugin->getName()] = $plugin->getVersion();
        }

        $container->setParameter('packages.registered_plugins', $plugins);
    }
}
