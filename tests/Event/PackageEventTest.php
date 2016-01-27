<?php

namespace Terramar\Packages\Tests\Event;

use Terramar\Packages\Entity\Package;
use Terramar\Packages\Event\PackageEvent;

class PackageEventTest extends \PHPUnit_Framework_TestCase
{
    /** @var Package */
    private $package;

    /** @var PackageEvent */
    private $sut;

    public function setUp()
    {
        $this->package = new Package();
        $this->sut = new PackageEvent($this->package);
    }

    public function testGetPackage()
    {
        $this->assertSame($this->package, $this->sut->getPackage());
    }
}
