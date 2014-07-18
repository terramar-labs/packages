<?php

namespace Terramar\Packages\Plugin\Satis;

use Doctrine\ORM\EntityManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Terramar\Packages\Event\PackageEvent;
use Terramar\Packages\Event\PackageUpdateEvent;
use Terramar\Packages\Events;
use Terramar\Packages\Helper\ResqueHelper;

class SatisPluginSubscriber implements EventSubscriberInterface
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
     * Constructor
     * 
     * @param ResqueHelper $resqueHelper
     */
    public function __construct(ResqueHelper $resqueHelper, EntityManager $entityManager)
    {
        $this->resqueHelper  = $resqueHelper;
        $this->entityManager = $entityManager;
    }

    /**
     * @param PackageUpdateEvent $event
     */
    public function onUpdatePackage(PackageUpdateEvent $event)
    {
        $package = $event->getPackage();
        $config = $this->entityManager->getRepository('Terramar\Packages\Plugin\Satis\PackageConfiguration')
            ->findOneBy(array('package' => $package));
        
        if (!$config || !$config->isEnabled()) {
            return;
        }
        
        $this->resqueHelper->enqueueOnce('default', 'Terramar\Packages\Plugin\Satis\UpdateAndBuildJob');
    }

    /**
     * @param PackageEvent $event
     */
    public function onEnablePackage(PackageEvent $event)
    {
        $package = $event->getPackage();
        $config = $this->entityManager->getRepository('Terramar\Packages\Plugin\Satis\PackageConfiguration')
            ->findOneBy(array('package' => $package));
        
        if (!$config) {
            $config = new PackageConfiguration();
            $config->setPackage($package);
        }
        
        $config->setEnabled(true);
        
        $this->entityManager->persist($config);
    }

    /**
     * @param PackageEvent $event
     */
    public function onDisablePackage(PackageEvent $event)
    {
        $package = $event->getPackage();
        $config = $this->entityManager->getRepository('Terramar\Packages\Plugin\Satis\PackageConfiguration')
            ->findOneBy(array('package' => $package));

        if (!$config) {
            $config = new PackageConfiguration();
            $config->setPackage($package);
        }

        $config->setEnabled(false);

        $this->entityManager->persist($config);
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return array(
            Events::PACKAGE_ENABLE  => array('onEnablePackage', 0),
            Events::PACKAGE_DISABLE => array('onDisablePackage', 0),
            Events::PACKAGE_UPDATE  => array('onUpdatePackage', 0)
        );
    }
}