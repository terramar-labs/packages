<?php

/*
 * Copyright (c) Terramar Labs
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Terramar\Packages\Event;

use Symfony\Component\EventDispatcher\Event;
use Terramar\Packages\Entity\Remote;

class RemoteEvent extends Event
{
    /**
     * @var Remote
     */
    private $remote;

    /**
     * Constructor.
     *
     * @param Remote $remote
     */
    public function __construct(Remote $remote)
    {
        $this->remote = $remote;
    }

    /**
     * @return Remote
     */
    public function getRemote()
    {
        return $this->remote;
    }
}
