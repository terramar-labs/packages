<?php

namespace Terramar\Packages\Event;

use Symfony\Component\EventDispatcher\Event;
use Terramar\Packages\Entity\Package;

class PackageUpdateEvent extends Event
{
    /**
     * @var Package
     */
    private $package;

    /**
     * @var mixed
     */
    private $payload;

    /**
     * Constructor
     * 
     * @param Package $package The updated package
     * @param mixed   $payload Any data received from the remote host
     */
    public function __construct(Package $package, $payload)
    {
        $this->package = $package;
        $this->payload = $payload;
    }

    /**
     * @return Package
     */
    public function getPackage()
    {
        return $this->package;
    }

    /**
     * @return mixed
     */
    public function getPayload()
    {
        return $this->payload;
    }
}