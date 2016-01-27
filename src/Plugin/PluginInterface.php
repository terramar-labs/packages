<?php

/*
 * Copyright (c) Terramar Labs
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Terramar\Packages\Plugin;

use Symfony\Component\DependencyInjection\ContainerBuilder;

interface PluginInterface
{
    /**
     * Configure the given ContainerBuilder.
     *
     * This method allows a plugin to register additional services with the
     * service container.
     *
     * @param ContainerBuilder $container
     */
    public function configure(ContainerBuilder $container);

    /**
     * Get the plugin name.
     * 
     * @return string
     */
    public function getName();

    /**
     * Get a string identifying the plugin's (or underlying tool's) version.
     * 
     * @return string|null
     */
    public function getVersion();
}
