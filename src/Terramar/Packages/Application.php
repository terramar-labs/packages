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
use Symfony\Component\Yaml\Yaml;
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
        
        $config = Yaml::parse(file_get_contents($this->getRootDir() . '/config.yml'));
        $security = isset($config['security']) ? $config['security'] : array();
        $doctrine = isset($config['doctrine']) ? $config['doctrine'] : array();
        $redis = isset($config['redis']) ? $config['redis'] : null;
        
        $this->appendExtension(new PackagesExtension(array(
                'output_dir' => $this->getRootDir() . '/web'
            )));
        $this->appendExtension(new DoctrineOrmExtension($doctrine));
        $this->appendExtension(new SessionExtension());
        $this->appendExtension(new TwigExtension($this->getRootDir() . '/views'));
        $this->appendExtension(new SecurityExtension(array(
                'username' => isset($security['username']) ? $security['username'] : null, 
                'password' => isset($security['password']) ? $security['password'] : null, 
                'firewall' => '^/manage',
                'success_path' => '/manage'
            )));
        if (isset($redis)) {
            $this->appendExtension(new CacheExtension(array(
                'connections' => array('default' => array(
                    'driver' => 'redis',
                    'options' => $redis
                )))));
        }
    }
}
