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
        $packages = isset($config['packages']) ? $config['packages'] : array();
        $doctrine = isset($config['doctrine']) ? $config['doctrine'] : array();
        
        $this->appendExtension(new PackagesExtension(array(
                'name' => isset($packages['name']) ? $packages['name'] : null,
                'homepage' => isset($packages['homepage']) ? $packages['homepage'] : null,
                'output_dir' => $this->getRootDir() . '/web',
            )));
        $this->appendExtension(new DoctrineOrmExtension($doctrine));
        $this->appendExtension(new SessionExtension());
        $this->appendExtension(new TwigExtension($this->getRootDir() . '/views'));
        $this->appendExtension(new SecurityExtension(array(
                'username' => isset($packages['admin_username']) ? $packages['admin_username'] : null, 
                'password' => isset($packages['admin_password']) ? $packages['admin_password'] : null, 
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
