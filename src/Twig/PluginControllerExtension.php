<?php

/*
 * Copyright (c) Terramar Labs
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Terramar\Packages\Twig;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Fragment\FragmentHandler;
use Symfony\Component\HttpKernel\Controller\ControllerReference;
use Terramar\Packages\Plugin\ControllerManagerInterface;

class PluginControllerExtension extends \Twig_Extension
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
     * @return array
     */
    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('render', array($this, 'render'), array('is_safe' => array('html'))),
            new \Twig_SimpleFunction('plugin_controllers', array($this, 'getControllers'))
        );
    }

    /**
     * Renders an action
     *
     * @param string|ControllerReference $uri
     *
     * @return string
     */
    public function render($uri)
    {
        return $this->handler->render($uri);
    }

    /**
     * @param $action
     *
     * @return array|ControllerReference
     */
    public function getControllers($action, $params = array())
    {
        $params['app'] = $this->request->get('app');
        return array_map(function($controller) use ($params) {
                return new ControllerReference($controller, $params);
            }, $this->manager->getControllers($action));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'plugin';
    }
}
