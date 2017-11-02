<?php

/*
 * Copyright (c) Terramar Labs
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Terramar\Packages\Plugin;

/**
 * A ControllerManager provides a registry for a set of controllers.
 */
interface ControllerManagerInterface
{
    /**
     * Register a controller for the given action.
     *
     * @param string $action
     * @param string $controller
     */
    public function registerController($action, $controller);

    /**
     * @param string $action
     *
     * @return array|callable[]
     */
    public function getControllers($action);
}
