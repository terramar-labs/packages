<?php

namespace Terramar\Packages\Job;

use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpKernel\KernelInterface;
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
    private $kernel = null;

    /**
     * @return ContainerInterface
     */
    protected function getContainer()
    {
        if ($this->kernel === null) {
            $this->kernel = $this->createApplication();
            $this->kernel->boot();
        }

        return $this->kernel->getContainer();
    }

    /**
     * @param array $kernelOptions
     */
    public function setKernelOptions(array $kernelOptions)
    {
        $this->args = \array_merge($this->args, $kernelOptions);
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
     * Perform the work
     */
    public function perform()
    {
        $this->run($this->args);
    }

    /**
     * Clean up the kernel
     */
    public function tearDown()
    {
        if ($this->kernel) {
            $this->kernel->shutdown();
        }
    }

    abstract protected function run($args);
}
