<?php

/*
 * Copyright (c) Terramar Labs
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Terramar\Packages\Plugin;

use Terramar\Packages\Router\RouteCollector;

/**
 * RouterPluginInterface defines the implementation of a plugin that also registers
 * routes with the RouteCollector.
 *
 * @see http://docs.terramarlabs.com/packages/3.2/plugins/creating-a-plugin
 */
interface RouterPluginInterface extends PluginInterface
{
    /**
     * Configure the given RouteCollector.
     *
     * This method allows a plugin to register additional HTTP routes with the
     * RouteCollector.
     *
     * @param RouteCollector $collector
     * @return void
     */
    public function collect(RouteCollector $collector);
}
