<?php

/*
 * Copyright (c) Terramar Labs
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Terramar\Packages\Helper;

use Doctrine\ORM\EntityManager;
use Gitlab\Model\Project;
use Nice\Router\UrlGeneratorInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Terramar\Packages\Entity\Remote;
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
        $this->adapters[$adapter->getName()] = $adapter;
    }

    /**
     * Synchronize packages in the given configuration
     * 
     * @param Remote $configuration
     *
     * @return \Terramar\Packages\Entity\Package[]
     */
    public function synchronizePackages(Remote $configuration)
    {
        $adapter = $this->getAdapter($configuration);
        
        $packages = $adapter->synchronizePackages($configuration);
        
        foreach ($packages as $package) {
            $event = new PackageEvent($package);
            $this->eventDispatcher->dispatch(Events::PACKAGE_CREATE, $event);
        }
        
        return $packages;
    }

    /**
     * @return array|SyncAdapterInterface[]
     */
    public function getAdapters()
    {
        return array_values($this->adapters);
    }
    
    private function getAdapter(Remote $configuration)
    {
        foreach ($this->adapters as $adapter) {
            if ($adapter->supports($configuration)) {
                return $adapter;
            }
        }
        
        throw new \RuntimeException('No adapter registered supports the given configuration');
    }
}