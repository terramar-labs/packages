<?php

namespace Terramar\Packages\Tests\Entity;

use Terramar\Packages\Entity\Package;
use Terramar\Packages\Entity\Remote;

class PackageTest extends \PHPUnit_Framework_TestCase
{
    /** @var Package */
    private $sut;

    public function setUp()
    {
        $this->sut = new Package();
    }

    public function testGetAndSetId()
    {
        $this->assertEquals(null, $this->sut->getId());

        // XXX Doctrine uses reflection, so there's no other way
        // to unit test this
        $reflProp = new \ReflectionProperty('Terramar\Packages\Entity\Package', 'id');
        $reflProp->setAccessible(true);
        $reflProp->setValue($this->sut, 345);
        $reflProp->setAccessible(false);
        $this->assertEquals(345, $this->sut->getId());
    }

    public function testIsAndSetEnabled()
    {
        $this->assertEquals(false, $this->sut->isEnabled());
        $this->sut->setEnabled(true);
        $this->assertEquals(true, $this->sut->isEnabled());
    }

    public function testGetAndSetDescription()
    {
        $this->assertEquals(null, $this->sut->getDescription());
        $this->sut->setDescription('foo');
        $this->assertEquals('foo', $this->sut->getDescription());
    }

    public function testGetAndSetName()
    {
        $this->assertEquals(null, $this->sut->getName());
        $this->sut->setName('bar');
        $this->assertEquals('bar', $this->sut->getName());
    }

    public function testGetAndSetRemote()
    {
        $this->assertEquals(null, $this->sut->getRemote());

        $expected = new Remote();
        $this->sut->setRemote($expected);
        $this->assertEquals($expected, $this->sut->getRemote());
    }

    public function testGetAndSetExternalId()
    {
        $this->assertEquals(null, $this->sut->getExternalId());
        $this->sut->setExternalId('1234erty');
        $this->assertEquals('1234erty', $this->sut->getExternalId());
    }

    public function testGetAndSetHookExternalId()
    {
        $this->assertEquals(null, $this->sut->getHookExternalId());
        $this->sut->setHookExternalId('rewq431');
        $this->assertEquals('rewq431', $this->sut->getHookExternalId());
    }

    public function testGetAndSetFqn()
    {
        $this->assertEquals(null, $this->sut->getFqn());
        $this->sut->setFqn('My FQN');
        $this->assertEquals('My FQN', $this->sut->getFqn());
    }

    public function testGetAndSetSshUrl()
    {
        $this->assertEquals(null, $this->sut->getSshUrl());
        $this->sut->setSshUrl('my.ssh.url');
        $this->assertEquals('my.ssh.url', $this->sut->getSshUrl());
    }

    public function testGetAndSetWebUrl()
    {
        $this->assertEquals(null, $this->sut->getWebUrl());
        $this->sut->setWebUrl('your.web.url');
        $this->assertEquals('your.web.url', $this->sut->getWebUrl());
    }
}
