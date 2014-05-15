<?php

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
                ->scalarNode('name')->isRequired()->end()
                ->scalarNode('homepage')->isRequired()->end()
                ->scalarNode('output_dir')->isRequired()->end()
            ->end();

        return $treeBuilder;
    }
}