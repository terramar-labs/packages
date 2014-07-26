<?php

namespace Terramar\Packages\Plugin;

interface ControllerManagerInterface
{
    /**
     * Register a controller for the given action
     * 
     * @param string $action
     * @param string $controller
     *
     * @return void
     */
    public function registerController($action, $controller);

    /**
     * @param string $action
     *
     * @return array|callable[]
     */
    public function getControllers($action);
}