<?php

namespace Terramar\Packages;

use Nice\Application as BaseApplication;
use Nice\Extension\CacheExtension;
use Nice\Extension\SecurityExtension;
use Nice\Extension\SessionExtension;
use Nice\Extension\TwigExtension;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Terramar\Packages\DependencyInjection\DoctrineOrmExtension;
use Terramar\Packages\DependencyInjection\PackagesExtension;

class Application extends BaseApplication
{
    /**
     * Register default extensions
     */
    protected function registerDefaultExtensions()
    {
        parent::registerDefaultExtensions();
        
        $this->appendExtension(new PackagesExtension());
        $this->appendExtension(new DoctrineOrmExtension());
        $this->appendExtension(new SessionExtension());
        $this->appendExtension(new TwigExtension($this->getRootDir() . '/views'));
        $this->appendExtension(new SecurityExtension(array(
                'username' => 'admin', 
                'password' => 'password', 
                'firewall' => '^/manage',
                'success_path' => '/manage'
            )));
        $this->appendExtension(new CacheExtension(array(
            'connections' => array('default' => array(
                'driver' => 'redis',
                'options' => array(
                    'socket' => '/tmp/redis.sock'
                )
            )))));
    }
}
