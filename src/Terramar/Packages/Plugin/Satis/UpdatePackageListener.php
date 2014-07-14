<?php

namespace Terramar\Packages\Plugin\Satis;

use Terramar\Packages\Event\PackageUpdateEvent;
use Terramar\Packages\Helper\ResqueHelper;

class UpdatePackageListener
{
    /**
     * @var ResqueHelper
     */
    private $helper;

    /**
     * Constructor
     * 
     * @param ResqueHelper $helper
     */
    public function __construct(ResqueHelper $helper)
    {
        $this->helper = $helper;
    }

    /**
     * @param PackageUpdateEvent $event
     */
    public function onUpdatePackage(PackageUpdateEvent $event)
    {
        $this->helper->enqueueOnce('default', 'Terramar\Packages\Plugin\Satis\UpdateAndBuildJob');
    }
}