<?php

/*
 * Copyright (c) Terramar Labs
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Terramar\Packages\Plugin;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;

/**
 * Defines the contract any CompilerAwarePluginInterface must implement
 *
 * A CompilerAwarePluginInterface is a plugin that also requires registration of
 * custom compiler passes.
 */
interface CompilerAwarePluginInterface extends PluginInterface
{
    /**
     * Gets the CompilerPasses this plugin requires.
     *
     * @return array|CompilerPassInterface[]
     */
    public function getCompilerPasses();
}
