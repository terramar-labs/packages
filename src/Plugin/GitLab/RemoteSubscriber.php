<?php

/*
 * Copyright (c) Terramar Labs
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Terramar\Packages\Plugin\GitLab;

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
     * Constructor
     *
     * @param SyncAdapter $adapter
     * @param EntityManager $entityManager
     */
    public function __construct(SyncAdapter $adapter, EntityManager $entityManager)
    {
        $this->adapter       = $adapter;
        $this->entityManager = $entityManager;
    }

    /**
     * @param RemoteEvent $event
     */
    public function onDisableRemote(RemoteEvent $event)
    {
        $remote = $event->getRemote();
        if ($remote->getAdapter() !== 'GitLab') {
            return;
        }

        $packages = $this->entityManager->getRepository('Terramar\Packages\Entity\Package')
            ->findBy(array('remote' => $remote));

        foreach ($packages as $package) {
            $this->adapter->disableHook($package);
            $package->setEnabled(false);
        }
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return array(
            Events::REMOTE_DISABLE => array('onDisableRemote', 255)
        );
    }
}
