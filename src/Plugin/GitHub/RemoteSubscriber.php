<?php

/*
 * Copyright (c) Terramar Labs
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Terramar\Packages\Plugin\GitHub;

use Doctrine\ORM\EntityManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Terramar\Packages\Event\RemoteEvent;
use Terramar\Packages\Events;

class RemoteSubscriber implements EventSubscriberInterface
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
            Events::REMOTE_DISABLE => ['onDisableRemote', 255],
        ];
    }

    /**
     * @param RemoteEvent $event
     */
    public function onDisableRemote(RemoteEvent $event)
    {
        $remote = $event->getRemote();
        if ($remote->getAdapter() !== 'GitHub') {
            return;
        }

        $packages = $this->entityManager->getRepository('Terramar\Packages\Entity\Package')
            ->findBy(['remote' => $remote]);

        foreach ($packages as $package) {
            $this->adapter->disableHook($package);
            $package->setEnabled(false);
        }
    }
}
