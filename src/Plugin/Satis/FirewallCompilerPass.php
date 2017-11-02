<?php

/*
 * Copyright (c) Terramar Labs
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Terramar\Packages\Plugin\Satis;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class FirewallCompilerPass implements CompilerPassInterface
{
    /**
     * You can modify the container here before it is dumped to PHP code.
     *
     * @param ContainerBuilder $container
     */
    public function process(ContainerBuilder $container)
    {
        $config = $container->getParameterBag()->resolveValue('%packages.configuration%');
        if (isset($config['secure_satis']) && $config['secure_satis'] === true) {
            $container->getDefinition('security.firewall_matcher')
                ->addMethodCall('matchPath', ['^/manage|^/packages(?!\.)']);
        }
    }
}