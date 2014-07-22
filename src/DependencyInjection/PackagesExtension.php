<?php

namespace Terramar\Packages\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Reference;
use Terramar\Packages\Events;

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
            ->addArgument(new Reference('event_dispatcher'));
        
        $container->setParameter('packages.configuration', array(
                'output_dir' => $config['output_dir']
            ));
        
        $container->register('packages.helper.resque', 'Terramar\Packages\Helper\ResqueHelper');
        
        $container->setParameter('packages.resque.host', $config['resque']['host']);
        $container->setParameter('packages.resque.port', $config['resque']['port']);
        $container->setParameter('packages.resque.database', $config['resque']['database']);

        $this->configureGitLabPlugin($container);
        $this->configureSatisPlugin($container);
        $this->configureCloneProjectPlugin($container);
        $this->configureSamiPlugin($container);
    }

    protected function configureGitLabPlugin(ContainerBuilder $container)
    {
        $container->register('packages.plugins.gitlab.adapter', 'Terramar\Packages\Plugin\GitLab\GitLabAdapter')
            ->addArgument(new Reference('doctrine.orm.entity_manager'))
            ->addArgument(new Reference('router.url_generator'));
        
        $container->getDefinition('packages.helper.sync')
            ->addMethodCall('registerAdapter', array(new Reference('packages.plugins.gitlab.adapter')));
        
        $container->register('packages.plugins.gitlab.subscriber', 'Terramar\Packages\Plugin\GitLab\GitLabPluginSubscriber')
            ->addArgument(new Reference('packages.plugins.gitlab.adapter'))
            ->addArgument(new Reference('doctrine.orm.entity_manager'))
            ->addTag('kernel.event_subscriber');
    }
    
    protected function configureSatisPlugin(ContainerBuilder $container)
    {
        $container->register('packages.plugins.satis.subscriber', 'Terramar\Packages\Plugin\Satis\SatisPluginSubscriber')
            ->addArgument(new Reference('packages.helper.resque'))
            ->addArgument(new Reference('doctrine.orm.entity_manager'))
            ->addTag('kernel.event_subscriber');
        
        $container->register('packages.plugins.satis.config_helper', 'Terramar\Packages\Plugin\Satis\ConfigurationHelper')
            ->addArgument(new Reference('doctrine.orm.entity_manager'))
            ->addArgument('%app.root_dir%');
    }

    protected function configureCloneProjectPlugin(ContainerBuilder $container)
    {
        $container->register('packages.plugins.clone_project.subscriber', 'Terramar\Packages\Plugin\CloneProject\CloneProjectPluginSubscriber')
            ->addArgument(new Reference('packages.helper.resque'))
            ->addArgument(new Reference('doctrine.orm.entity_manager'))
            ->addTag('kernel.event_subscriber');
    }

    protected function configureSamiPlugin(ContainerBuilder $container)
    {
        $container->register('packages.plugins.sami.subscriber', 'Terramar\Packages\Plugin\Sami\SamiPluginSubscriber')
            ->addArgument(new Reference('packages.helper.resque'))
            ->addArgument(new Reference('doctrine.orm.entity_manager'))
            ->addTag('kernel.event_subscriber');
    }
}