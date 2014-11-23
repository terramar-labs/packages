<?php

namespace Terramar\Packages\Plugin\GitHub;

use Doctrine\ORM\EntityManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Terramar\Packages\Event\PackageEvent;
use Terramar\Packages\Event\PackageUpdateEvent;
use Terramar\Packages\Events;
use Terramar\Packages\Helper\ResqueHelper;
use Terramar\Packages\Helper\SyncHelper;

class EventSubscriber implements EventSubscriberInterface
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
     * @param PackageEvent $event
     */
    public function onCreatePackage(PackageEvent $event)
    {
        $package = $event->getPackage();
        $config = $this->entityManager->getRepository('Terramar\Packages\Plugin\GitHub\PackageConfiguration')
            ->findOneBy(array('package' => $package));

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
        if ($package->getRemote()->getAdapter() !== 'GitHub') {
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
        if ($package->getRemote()->getAdapter() !== 'GitHub') {
            return;
        }

        $this->adapter->disableHook($package);
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return array(
            Events::PACKAGE_CREATE  => array('onCreatePackage', 255),
            Events::PACKAGE_ENABLE  => array('onEnablePackage', 255),
            Events::PACKAGE_DISABLE => array('onDisablePackage', 255)
        );
    }
}
