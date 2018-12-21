<?php

/*
 * Copyright (c) Terramar Labs
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Terramar\Packages;

use Nice\Application as BaseApplication;
use Nice\Extension\DoctrineOrmExtension;
use Nice\Extension\SecurityExtension;
use Nice\Extension\SessionExtension;
use Nice\Extension\TemplatingExtension;
use Nice\Extension\TwigExtension;
use Symfony\Component\Yaml\Yaml;
use Terramar\Packages\DependencyInjection\PackagesExtension;
use Terramar\Packages\Plugin\CloneProject\Plugin as CloneProjectPlugin;
use Terramar\Packages\Plugin\GitHub\Plugin as GitHubPlugin;
use Terramar\Packages\Plugin\GitLab\Plugin as GitLabPlugin;
use Terramar\Packages\Plugin\Bitbucket\Plugin as BitbucketPlugin;
use Terramar\Packages\Plugin\PluginInterface;
use Terramar\Packages\Plugin\Sami\Plugin as SamiPlugin;
use Terramar\Packages\Plugin\Satis\Plugin as SatisPlugin;

class Application extends BaseApplication
{
    /**
     * @var array|PluginInterface[]
     */
    private $plugins = [];

    /**
     * Register default extensions.
     */
    protected function registerDefaultExtensions()
    {
        parent::registerDefaultExtensions();

        $this->registerDefaultPlugins();

        $config = Yaml::parse(file_get_contents($this->getRootDir() . '/config.yml'));
        $security = isset($config['security']) ? $config['security'] : [];
        $doctrine = isset($config['doctrine']) ? $config['doctrine'] : [];
        $packages = isset($config['packages']) ? $config['packages'] : [];
        if (!isset($packages['resque'])) {
            $packages['resque'] = [];
        }

        $this->appendExtension(new PackagesExtension($this->plugins, $packages));
        $this->appendExtension(new DoctrineOrmExtension($doctrine));
        $this->appendExtension(new SessionExtension());
        $this->appendExtension(new TemplatingExtension());
        $this->appendExtension(new TwigExtension());
        $this->appendExtension(new SecurityExtension([
            'authenticator' => [
                'type'     => 'username',
                'username' => isset($security['username']) ? $security['username'] : null,
                'password' => isset($security['password'])
                    ? password_hash($security['password'], PASSWORD_DEFAULT)
                    : null,
            ],
            'firewall'      => '^/manage',
            'success_path'  => '/manage',
        ]));
    }

    /**
     * Register default plugins.
     */
    protected function registerDefaultPlugins()
    {
        $this->registerPlugin(new GitLabPlugin());
        $this->registerPlugin(new GitHubPlugin());
        $this->registerPlugin(new BitbucketPlugin());
        $this->registerPlugin(new CloneProjectPlugin());
        $this->registerPlugin(new SamiPlugin());
        $this->registerPlugin(new SatisPlugin());
    }

    /**
     * Register a Plugin with the Application.
     *
     * @param PluginInterface $plugin
     */
    public function registerPlugin(PluginInterface $plugin)
    {
        $this->plugins[$plugin->getName()] = $plugin;
    }
}
