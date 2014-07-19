<?php

namespace Terramar\Packages\Plugin\Sami;

use Doctrine\ORM\EntityManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Terramar\Packages\Event\PackageEvent;
use Terramar\Packages\Events;
use Terramar\Packages\Helper\ResqueHelper;
use Terramar\Packages\Plugin\CloneProject\Events as CloneProjectEvents;
use Terramar\Packages\Plugin\CloneProject\PackageCloneEvent;

class SamiPluginSubscriber implements EventSubscriberInterface
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
     * @param PackageCloneEvent $event
     */
    public function onClonePackage(PackageCloneEvent $event)
    {
        $package = $event->getPackage();
        $config = $this->entityManager->getRepository('Terramar\Packages\Plugin\Sami\PackageConfiguration')
            ->findOneBy(array('package' => $package));
        
        if (!$config || !$config->isEnabled()) {
            return;
        }
        
        $config->setRepositoryPath($event->getRepositoryPath());

        $this->entityManager->persist($config);
        $this->entityManager->flush($config);
        
        $this->resqueHelper->enqueue('default', 'Terramar\Packages\Plugin\Sami\UpdateJob', array('id' => $package->getId()));
    }

    /**
     * @param PackageEvent $event
     */
    public function onEnablePackage(PackageEvent $event)
    {
        $package = $event->getPackage();
        $config = $this->entityManager->getRepository('Terramar\Packages\Plugin\Sami\PackageConfiguration')
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
        $config = $this->entityManager->getRepository('Terramar\Packages\Plugin\Sami\PackageConfiguration')
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
            Events::PACKAGE_ENABLE             => array('onEnablePackage', 0),
            Events::PACKAGE_DISABLE            => array('onDisablePackage', 0),
            CloneProjectEvents::PACKAGE_CLONED => array('onClonePackage', 0)
        );
    }
}