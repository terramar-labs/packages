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
use Terramar\Packages\Plugin\RouterPluginInterface;

class PackagesExtension extends Extension
{
    /**
     * @var array
     */
    private $options = [];

    /**
     * @var array|PluginInterface[]
     */
    private $plugins = [];

    /**
     * Constructor.
     *
     * @param array $plugins
     * @param array $options
     */
    public function __construct(array $plugins = [], array $options = [])
    {
        $this->plugins = $plugins;
        $this->options = $options;
    }

    /**
     * Loads a specific configuration.
     *
     * @param array $configs An array of configuration values
     * @param ContainerBuilder $container A ContainerBuilder instance
     *
     * @throws \InvalidArgumentException When provided tag is not defined in this extension
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configs[] = $this->options;
        $configuration = $this->getConfiguration($configs, $container);
        $config = $this->processConfiguration($configuration, $configs);

        $container->setParameter('packages.configuration', [
            'name'          => empty($config['name']) ? $config['site_name'] : $config['name'],
            'homepage'      => $config['homepage'],
            'base_path'     => $config['base_path'],
            'archive'       => $config['archive'],
            'output_dir'    => $container->getParameterBag()->resolveValue($config['output_dir']),
            'contact_email' => $config['contact_email'],
            'secure_satis'  => $config['secure_satis'],
        ]);

        $collector = $container->register('router.collector', 'Terramar\Packages\Router\RouteCollector')
            ->addArgument(new Reference('router.parser'))
            ->addArgument(new Reference('router.data_generator'));

        $container->register('packages.command_registry', 'Terramar\Packages\Console\CommandRegistry');

        $container->register('packages.helper.sync', 'Terramar\Packages\Helper\SyncHelper')
            ->addArgument(new Reference('event_dispatcher'));

        $container->register('packages.helper.resque', 'Terramar\Packages\Helper\ResqueHelper');

        $container->setParameter('packages.resque.host', $config['resque']['host']);
        $container->setParameter('packages.resque.port', $config['resque']['port']);
        $container->setParameter('packages.resque.database', $config['resque']['database']);

        $container->register('packages.controller_manager', 'Terramar\Packages\Plugin\ControllerManager');

        $container->register('packages.fragment_handler.inline_renderer',
            'Symfony\Component\HttpKernel\Fragment\InlineFragmentRenderer')
            ->addArgument(new Reference('http_kernel'))
            ->addArgument(new Reference('event_dispatcher'));
        $container->register('packages.fragment_handler', 'Symfony\Component\HttpKernel\Fragment\FragmentHandler')
            ->addArgument([
                new Reference('packages.fragment_handler.inline_renderer'),
            ])
            ->addArgument(false)
            ->addMethodCall('setRequest', [
                new Reference(
                    'request',
                    ContainerInterface::NULL_ON_INVALID_REFERENCE,
                    false
                ),
            ]);

        $container->register('packages.twig_extension.packages_conf', 'Terramar\Packages\Twig\PackagesConfigExtension')
            ->addArgument('%packages.configuration%')
            ->addTag('twig.extension');

        $container->register('packages.twig_extension.plugin', 'Terramar\Packages\Twig\PluginControllerExtension')
            ->addArgument(new Reference('packages.controller_manager'))
            ->addArgument(new Reference('packages.fragment_handler'))
            ->addMethodCall('setRequest', [
                new Reference(
                    'request',
                    ContainerInterface::NULL_ON_INVALID_REFERENCE,
                    false
                ),
            ])
            ->addTag('twig.extension');

        $container->register('packages.fragment_handler.uri_signer', 'Symfony\Component\HttpKernel\UriSigner')
            ->addArgument('');

        $container->register('packages.fragment_handler.listener',
            'Symfony\Component\HttpKernel\EventListener\FragmentListener')
            ->addArgument(new Reference('packages.fragment_handler.uri_signer'))
            ->addTag('kernel.event_subscriber');

        $container->register('packages.helper.plugin', 'Terramar\Packages\Helper\PluginHelper')
            ->addArgument(new Reference('packages.controller_manager'))
            ->addArgument(new Reference('packages.fragment_handler'))
            ->addMethodCall('setRequest', [
                new Reference(
                    'request',
                    ContainerInterface::NULL_ON_INVALID_REFERENCE,
                    false
                ),
            ]);

        $plugins = [];
        foreach ($this->plugins as $plugin) {
            $name = preg_replace('/[^a-z0-9]/', '', strtolower($plugin->getName()));
            $container->register('packages.plugin.'.$name, get_class($plugin));
            $plugin->configure($container);
            $plugins[$name] = $plugin->getVersion();
            if ($plugin instanceof RouterPluginInterface) {
                $collector->addMethodCall('registerPlugin', [new Reference('packages.plugin.'.$name)]);
            }
        }

        $container->setParameter('packages.registered_plugins', $plugins);
    }

    /**
     * Returns extension configuration.
     *
     * @param array $config An array of configuration values
     * @param ContainerBuilder $container A ContainerBuilder instance
     *
     * @return PackagesConfiguration
     */
    public function getConfiguration(array $config, ContainerBuilder $container)
    {
        return new PackagesConfiguration();
    }
}
