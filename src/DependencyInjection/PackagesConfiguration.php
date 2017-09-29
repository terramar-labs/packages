<?php

/*
 * Copyright (c) Terramar Labs
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Terramar\Packages\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class PackagesConfiguration implements ConfigurationInterface
{
    /**
     * Generates the configuration tree builder.
     *
     * @return \Symfony\Component\Config\Definition\Builder\TreeBuilder The tree builder
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('packages');

        $rootNode
            ->children()
                ->scalarNode('site_name')->defaultValue('Private Composer Repository')->end()
                ->scalarNode('name')->defaultNull()->end()
                ->scalarNode('homepage')->defaultValue('')->end()
                ->scalarNode('contact_email')->defaultValue('')->end()
                ->scalarNode('output_dir')->defaultValue('%app.root_dir%/web')->end()
                ->arrayNode('resque')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('host')->defaultValue('localhost')->end()
                        ->scalarNode('port')->defaultValue('6379')->end()
                        ->scalarNode('database')->defaultNull()->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
