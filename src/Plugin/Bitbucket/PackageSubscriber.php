<?php

/*
 * Copyright (c) Terramar Labs
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Terramar\Packages\Plugin\Bitbucket;

use Doctrine\ORM\EntityManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Terramar\Packages\Event\PackageEvent;
use Terramar\Packages\Events;

class PackageSubscriber implements EventSubscriberInterface
{
    /**
     * @var SyncAdapter
     */
    private $adapter;

    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * Constructor.
     *
     * @param SyncAdapter $adapter
     * @param EntityManager $entityManager
     */
    public function __construct(SyncAdapter $adapter, EntityManager $entityManager)
    {
        $this->adapter = $adapter;
        $this->entityManager = $entityManager;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            Events::PACKAGE_CREATE => ['onCreatePackage', 255],
            Events::PACKAGE_ENABLE => ['onEnablePackage', 255],
            Events::PACKAGE_DISABLE => ['onDisablePackage', 255],
        ];
    }

    /**
     * @param PackageEvent $event
     */
    public function onCreatePackage(PackageEvent $event)
    {
        $package = $event->getPackage();
        $config = $this->entityManager->getRepository('Terramar\Packages\Plugin\Bitbucket\PackageConfiguration')
            ->findOneBy(['package' => $package]);

        if (!$config) {
            $config = new PackageConfiguration();
            $config->setPackage($package);
        }

        $this->entityManager->persist($config);
    }

    /**
     * @param PackageEvent $event
     */
    public function onEnablePackage(PackageEvent $event)
    {
        $package = $event->getPackage();
        if ($package->getRemote()->getAdapter() !== 'Bitbucket') {
            return;
        }

        $this->adapter->enableHook($package);
    }

    /**
     * @param PackageEvent $event
     */
    public function onDisablePackage(PackageEvent $event)
    {
        $package = $event->getPackage();
        if ($package->getRemote()->getAdapter() !== 'Bitbucket') {
            return;
        }

        $this->adapter->disableHook($package);
    }
}
