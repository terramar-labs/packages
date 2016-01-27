<?php
namespace Terramar\Packages\Tests\Helper;

use Terramar\Packages\Helper\PluginHelper;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ControllerReference;
use Symfony\Component\HttpKernel\Fragment\FragmentHandler;
use Terramar\Packages\Plugin\ControllerManagerInterface;

class PluginHelperTest extends \PHPUnit_Framework_TestCase
{

    /** @var ControllerManagerInterface|\PHPUnit_Framework_MockObject_MockObject */
    private $controllerManager;

    /** @var FragmentHandler|\PHPUnit_Framework_MockObject_MockObject */
    private $fragmentHandler;

    /** @var PluginHelper */
    private $sut;

    public function setUp()
    {
        $this->controllerManager = $this->getMock('Terramar\Packages\Plugin\ControllerManagerInterface');
        $this->fragmentHandler = $this->getMock('Symfony\Component\HttpKernel\Fragment\FragmentHandler');
        $this->sut = new PluginHelper($this->controllerManager, $this->fragmentHandler);
    }

    public function testInvokeAction()
    {
        $controllerRefCount = 0;
        $controllerList = array(
            'C1',
            'B1',
            'A5',
            'D2'
        );
        $action = 'awesomeAction';
        $params = array(
            'goo' => 'boo'
        );
        
        $request = new Request($query = array(
            'foo' => 'bar',
            'baz' => 'shoo'
        ), $requestParams = array(
            'a' => 'kaboom',
            'd' => 'achoo'
        ));
        
        $memberRequest = new Request(array(), array(), $attributes = array(
            'app' => 'foobarbaz'
        ));
        $this->sut->setRequest($memberRequest);
        
        $mergedParams = array_merge(array(), $params, $attributes);
        $mergedQuery = array_merge(array(), $query, $requestParams);
        
        $this->controllerManager->expects($this->any())
            ->method('getControllers')
            ->with($action)
            ->will($this->returnValue($controllerList));
        
        $controllersAdded = array();
        
        $this->fragmentHandler->expects($this->exactly(count($controllerList)))
            ->method('render')
            ->with($this->logicalAnd($this->isInstanceOf('Symfony\Component\HttpKernel\Controller\ControllerReference'), $this->callback(function (ControllerReference $cr) use(&$controllersAdded, $controllerList, $mergedParams, $mergedQuery) {
            $controllersAdded[] = $cr->controller;
            // XXX PHPUnit bug prevents us from doing an order-based test here
            // TODO upgrade PHPUnit
            return in_array($cr->controller, $controllerList) && count(array_diff_assoc($mergedParams, $cr->attributes)) < 1 && count(array_diff_assoc($mergedQuery, $cr->query)) < 1;
        })));
        
        $this->sut->invokeAction($request, $action, $params);
        
        $this->assertEmpty(array_diff($controllerList, array_unique($controllersAdded)));
    }
}