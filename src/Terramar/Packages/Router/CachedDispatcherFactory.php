<?php

namespace Terramar\Packages\Router;

use FastRoute\Dispatcher;
use FastRoute\RouteCollector;
use Nice\Router\DispatcherFactoryInterface;
use Symfony\Component\Config\ConfigCache;

class CachedDispatcherFactory implements DispatcherFactoryInterface
{
    /**
     * @var \FastRoute\RouteCollector
     */
    private $collector;

    /**
     * @var string
     */
    private $cacheFile;

    /**
     * @var bool
     */
    private $debug;

    /**
     * Constructor
     * 
     * @param RouteCollector $collector
     * @param string         $cacheFile
     * @param bool           $debug
     */
    public function __construct(RouteCollector $collector, $cacheFile, $debug = false)
    {
        $this->collector = $collector;
        $this->cacheFile = $cacheFile;
        $this->debug     = (bool) $debug;
    }
    
    /**
     * Create a dispatcher
     *
     * @return Dispatcher
     */
    public function create()
    {
        $cache = new ConfigCache($this->cacheFile, $this->debug);
        if (!$cache->isFresh()) {
            $this->collector->addRoute('GET', '/', array('Terramar\Packages\Controller\DefaultController', 'indexAction'));
            
            $cache->write('<?php return ' . var_export($this->collector->getData(), true) . ';');
        }

        $routes = require_once $cache;
        
        return new Dispatcher\GroupCountBased($routes);
    }
}