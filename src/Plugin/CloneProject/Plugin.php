<?php

/*
 * Copyright (c) Terramar Labs
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Terramar\Packages\Plugin\CloneProject;

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
        $container->register('packages.plugin.clone_project.subscriber', 'Terramar\Packages\Plugin\CloneProject\EventSubscriber')
            ->addArgument(new Reference('packages.helper.resque'))
            ->addArgument(new Reference('doctrine.orm.entity_manager'))
            ->addTag('kernel.event_subscriber');

        $container->getDefinition('packages.controller_manager')
            ->addMethodCall('registerController', array(Actions::PACKAGE_EDIT, 'Terramar\Packages\Plugin\CloneProject\Controller::editAction'))
            ->addMethodCall('registerController', array(Actions::PACKAGE_UPDATE, 'Terramar\Packages\Plugin\CloneProject\Controller::updateAction'));
    }

    /**
     * Get the plugin name
     *
     * @return string
     */
    public function getName()
    {
        return 'git-clone';
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