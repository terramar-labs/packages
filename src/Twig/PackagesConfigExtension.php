<?php

/*
 * Copyright (c) Terramar Labs
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Terramar\Packages\Twig;

/**
 * The PluginControllerExtension provides helper functions for getting
 * Packages configuration values.
 */
class PackagesConfigExtension extends \Twig_Extension
{
    /**
     * @var array
     */
    private $config;

    /**
     * Constructor.
     *
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->config = $config;
    }

    /**
     * @return array
     */
    public function getGlobals()
    {
        return [
            'packages_conf' => [
                'name'          => $this->config['name'],
                'homepage'      => $this->config['homepage'],
                'contact_email' => $this->config['contact_email'],
            ],
        ];
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'packages_conf';
    }
}
