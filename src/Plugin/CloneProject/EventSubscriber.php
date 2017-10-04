<?php

/*
 * Copyright (c) Terramar Labs
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Terramar\Packages\Plugin\CloneProject;

use Doctrine\ORM\EntityManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Terramar\Packages\Event\PackageEvent;
use Terramar\Packages\Event\PackageUpdateEvent;
use Terramar\Packages\Events;
use Terramar\Packages\Helper\ResqueHelper;

class EventSubscriber implements EventSubscriberInterface
{
    /**
     * @var ResqueHelper
     */
    private $resqueHelper;

    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * Constructor.
     *
     * @param ResqueHelper $resqueHelper
     * @param EntityManager $entityManager
     */
    public function __construct(ResqueHelper $resqueHelper, EntityManager $entityManager)
    {
        $this->resqueHelper = $resqueHelper;
        $this->entityManager = $entityManager;
    }

    /**
     * @param PackageUpdateEvent $event
     */
    public function onUpdatePackage(PackageUpdateEvent $event)
    {
        $package = $event->getPackage();
        /** @var \Terramar\Packages\Plugin\CloneProject\PackageConfiguration $config */
        $config = $this->entityManager->getRepository('Terramar\Packages\Plugin\CloneProject\PackageConfiguration')
            ->findOneBy(['package' => $package]);

        if ( ! $config || ! $config->isEnabled() || ! $package->isEnabled()) {
            return;
        }

        $this->resqueHelper->enqueue('default', 'Terramar\Packages\Plugin\CloneProject\CloneProjectJob', ['id' => $event->getPackage()->getId()]);
    }

    /**
     * @param PackageEvent $event
     */
    public function onCreatePackage(PackageEvent $event)
    {
        $package = $event->getPackage();
        $config = $this->entityManager->getRepository('Terramar\Packages\Plugin\CloneProject\PackageConfiguration')
            ->findOneBy(['package' => $package]);

        if ( ! $config) {
            $config = new PackageConfiguration();
            $config->setPackage($package);
        }

        $this->entityManager->persist($config);
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            Events::PACKAGE_CREATE => [
                'onCreatePackage',
                0,
            ],
            Events::PACKAGE_UPDATE => [
                'onUpdatePackage',
                0,
            ],
        ];
    }
}
