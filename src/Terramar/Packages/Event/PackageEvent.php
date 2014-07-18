<?php

namespace Terramar\Packages\Event;

use Symfony\Component\EventDispatcher\Event;
use Terramar\Packages\Entity\Package;

class PackageEvent extends Event
{
    /**
     * @var Package
     */
    private $package;
    
    /**
     * Constructor
     *
     * @param Package $package
     */
    public function __construct(Package $package)
    {
        $this->package = $package;
    }

    /**
     * @return Package
     */
    public function getPackage()
    {
        return $this->package;
    }
}