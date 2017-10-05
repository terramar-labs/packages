<?php

/*
 * Copyright (c) Terramar Labs
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Terramar\Packages\Event;

use Terramar\Packages\Entity\Package;

class PackageUpdateEvent extends PackageEvent
{
    /**
     * @var mixed
     */
    private $payload;

    /**
     * Constructor.
     *
     * @param Package $package The updated package
     * @param mixed $payload Any data received from the remote host
     */
    public function __construct(Package $package, $payload)
    {
        $this->payload = $payload;

        parent::__construct($package);
    }

    /**
     * @return mixed
     */
    public function getPayload()
    {
        return $this->payload;
    }
}
