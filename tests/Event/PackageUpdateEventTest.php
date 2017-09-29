<?php

/*
 * Copyright (c) Terramar Labs
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Terramar\Packages\Tests\Event;

use Terramar\Packages\Entity\Remote;
use Terramar\Packages\Event\RemoteEvent;

class PackageUpdateEventTest extends \PHPUnit_Framework_TestCase
{
    /** @var Remote */
    private $remote;

    /** @var RemoteEvent */
    private $sut;

    public function setUp()
    {
        $this->remote = new Remote();
        $this->sut = new RemoteEvent($this->remote);
    }

    public function testGetRemote()
    {
        $this->assertSame($this->remote, $this->sut->getRemote());
    }
}
