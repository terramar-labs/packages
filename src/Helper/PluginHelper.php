<?php

/*
 * Copyright (c) Terramar Labs
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Terramar\Packages\Helper;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ControllerReference;
use Symfony\Component\HttpKernel\Fragment\FragmentHandler;
use Terramar\Packages\Plugin\ControllerManagerInterface;

class PluginHelper
{
    /**
     * @var ControllerManagerInterface
     */
    private $manager;

    /**
     * @var FragmentHandler
     */
    private $handler;

    /**
     * @var Request
     */
    private $request;

    /**
     * Constructor.
     *
     * @param ControllerManagerInterface $manager
     * @param FragmentHandler            $handler
     */
    public function __construct(ControllerManagerInterface $manager, FragmentHandler $handler)
    {
        $this->manager = $manager;
        $this->handler = $handler;
    }

    /**
     * @param Request $request
     */
    public function setRequest(Request $request = null)
    {
        $this->request = $request;
    }

    /**
     * Invoke the given action using the given Request.
     * 
     * @param Request $request
     * @param string  $action
     */
    public function invokeAction(Request $request, $action, $params = array())
    {
        $controllers = $this->getControllers($action, $params, array_merge($request->query->all(), $request->request->all()));

        foreach ($controllers as $controller) {
            $this->handler->render($controller);
        }
    }

    /**
     * @param $action
     *
     * @return array|ControllerReference[]
     */
    private function getControllers($action, $params = array(), $query = array())
    {
        $params['app'] = $this->request->get('app');

        return array_map(function ($controller) use ($params, $query) {
                return new ControllerReference($controller, $params, $query);
            }, $this->manager->getControllers($action));
    }
}
