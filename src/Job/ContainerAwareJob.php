<?php

/*
 * Copyright (c) Terramar Labs
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Terramar\Packages\Job;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Terramar\Packages\Application;

abstract class ContainerAwareJob
{
    /**
     * @var \Resque_Job
     */
    public $job;

    /**
     * @var string The queue name
     */
    public $queue = 'default';

    /**
     * @var array The job args
     */
    public $args = array();

    /**
     * @var Application
     */
    private $app = null;

    /**
     * @return ContainerInterface
     */
    protected function getContainer()
    {
        if ($this->app === null) {
            $this->app = $this->createApplication();
            $this->app->boot();
        }

        return $this->app->getContainer();
    }

    /**
     * @return Application
     */
    protected function createApplication()
    {
        return new Application(
            isset($this->args['app.environment']) ? $this->args['app.environment'] : 'dev',
            isset($this->args['app.debug']) ? $this->args['app.debug'] : true,
            isset($this->args['app.cache']) ? $this->args['app.cache'] : true
        );
    }

    /**
     * Perform the work.
     */
    public function perform()
    {
        $this->run($this->args);
    }

    abstract protected function run($args);
}
