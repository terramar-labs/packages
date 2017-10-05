<?php

/*
 * Copyright (c) Terramar Labs
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Terramar\Packages\Plugin\Sami;

use Doctrine\ORM\EntityManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Terramar\Packages\Event\PackageEvent;
use Terramar\Packages\Events;
use Terramar\Packages\Helper\ResqueHelper;
use Terramar\Packages\Plugin\CloneProject\Events as CloneProjectEvents;
use Terramar\Packages\Plugin\CloneProject\PackageCloneEvent;

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
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            Events::PACKAGE_CREATE             => ['onCreatePackage', 0],
            CloneProjectEvents::PACKAGE_CLONED => ['onClonePackage', 0],
        ];
    }

    /**
     * @param PackageCloneEvent $event
     */
    public function onClonePackage(PackageCloneEvent $event)
    {
        $package = $event->getPackage();
        $config = $this->entityManager->getRepository('Terramar\Packages\Plugin\Sami\PackageConfiguration')
            ->findOneBy(['package' => $package]);

        if (!$config || !$config->isEnabled() || !$package->isEnabled()) {
            return;
        }

        $config->setRepositoryPath($event->getRepositoryPath());

        $this->entityManager->persist($config);
        $this->entityManager->flush($config);

        $this->resqueHelper->enqueue('default', 'Terramar\Packages\Plugin\Sami\UpdateJob', ['id' => $package->getId()]);
    }

    /**
     * @param PackageEvent $event
     */
    public function onCreatePackage(PackageEvent $event)
    {
        $package = $event->getPackage();
        $config = $this->entityManager->getRepository('Terramar\Packages\Plugin\Sami\PackageConfiguration')
            ->findOneBy(['package' => $package]);

        if (!$config) {
            $config = new PackageConfiguration();
            $config->setPackage($package);
        }

        $this->entityManager->persist($config);
    }
}
