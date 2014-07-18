<?php

namespace Terramar\Packages\Helper;

use Doctrine\ORM\EntityManager;
use Gitlab\Model\Project;
use Nice\Router\UrlGeneratorInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Terramar\Packages\Entity\Configuration;
use Terramar\Packages\Entity\Package;
use Terramar\Packages\Event\PackageEvent;
use Terramar\Packages\Events;

class SyncHelper
{

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * @var array|SyncAdapterInterface[]
     */
    private $adapters = array();

    /**
     * Constructor
     * 
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function __construct(EventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * Register an adapter with the helper
     * 
     * @param SyncAdapterInterface $adapter
     */
    public function registerAdapter(SyncAdapterInterface $adapter)
    {
        if (!in_array($adapter, $this->adapters, true)) {
            $this->adapters[] = $adapter;
        }
    }

    /**
     * Synchronize packages in the given configuration
     * 
     * @param Configuration $configuration
     *
     * @return \Terramar\Packages\Entity\Package[]
     */
    public function synchronizePackages(Configuration $configuration)
    {
        $adapter = $this->getAdapter($configuration);
        
        $packages = $adapter->synchronizePackages($configuration);
        
        foreach ($packages as $package) {
            $event = new PackageEvent($package);
            $this->eventDispatcher->dispatch(Events::PACKAGE_CREATE, $event);
        }
        
        return $packages;
    }
    
    private function getAdapter(Configuration $configuration)
    {
        foreach ($this->adapters as $adapter) {
            if ($adapter->supports($configuration)) {
                return $adapter;
            }
        }
        
        throw new \RuntimeException('No adapter registered supports the given configuration');
    }
}