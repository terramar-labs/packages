<?php

/*
 * Copyright (c) Terramar Labs
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Terramar\Packages\Twig;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ControllerReference;
use Symfony\Component\HttpKernel\Fragment\FragmentHandler;
use Terramar\Packages\Plugin\ControllerManagerInterface;

/**
 * The PluginControllerExtension provides plugin related functionality to twig.
 */
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
     * @param FragmentHandler $handler
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
        return [
            new \Twig_SimpleFunction('render', [$this, 'render'], ['is_safe' => ['html']]),
            new \Twig_SimpleFunction('plugin_controllers', [$this, 'getControllers']),
            new \Twig_SimpleFunction('md5', 'md5'),
        ];
    }

    /**
     * Renders an action.
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
     * @param array $params
     * @return array|ControllerReference
     */
    public function getControllers($action, $params = [])
    {
        $params['app'] = $this->request->get('app');

        return array_map(function ($controller) use ($params) {
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
