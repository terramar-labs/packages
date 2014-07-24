<?php

namespace Terramar\Packages\Plugin\GitLab;

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
        $this->adapter  = $adapter;
        $this->entityManager = $entityManager;
    }

    /**
     * @param PackageEvent $event
     */
    public function onEnablePackage(PackageEvent $event)
    {
        $package = $event->getPackage();
        $config = $this->entityManager->getRepository('Terramar\Packages\Plugin\GitLab\PackageConfiguration')
            ->findOneBy(array('package' => $package));
        
        if (!$config) {
            $config = new PackageConfiguration();
            $config->setPackage($package);
        }
        
        $config->setEnabled(true);
        $this->adapter->enableHook($package);
        
        $this->entityManager->persist($config);
    }

    /**
     * @param PackageEvent $event
     */
    public function onDisablePackage(PackageEvent $event)
    {
        $package = $event->getPackage();
        $config = $this->entityManager->getRepository('Terramar\Packages\Plugin\GitLab\PackageConfiguration')
            ->findOneBy(array('package' => $package));

        if (!$config) {
            $config = new PackageConfiguration();
            $config->setPackage($package);
        }

        $config->setEnabled(false);
        $this->adapter->disableHook($package);

        $this->entityManager->persist($config);
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return array(
            Events::PACKAGE_ENABLE  => array('onEnablePackage', 255),
            Events::PACKAGE_DISABLE => array('onDisablePackage', 255)
        );
    }
}