<?php
namespace Terramar\Packages\Unit\Entity;

use Terramar\Packages\Entity\Remote;

class RemoteTest extends \PHPUnit_Framework_TestCase
{
	/** @var Package */
	private $sut;
	
	public function setUp()
	{
		$this->sut = new Remote();
	}
	
	public function testGetAndSetId()
	{
		$this->assertEquals(null, $this->sut->getId());
		
		// XXX Doctrine uses reflection, so there's no other way
		// to unit test this
		$reflProp = new \ReflectionProperty('Terramar\Packages\Entity\Remote', 'id');
		$reflProp->setAccessible(true);
		$reflProp->setValue($this->sut, 345);
		$reflProp->setAccessible(false);
		$this->assertEquals(345, $this->sut->getId());
	}
	
	public function testIsAndSetEnabled()
	{
		$this->assertEquals(true, $this->sut->isEnabled());
		$this->sut->setEnabled(false);
		$this->assertEquals(false, $this->sut->isEnabled());
	}
	
	public function testGetAndSetAdapter()
	{
		$this->assertEquals(null, $this->sut->getAdapter());
		$this->sut->setAdapter('foo');
		$this->assertEquals('foo', $this->sut->getAdapter());
	}
	
	public function testGetAndSetName()
	{
		$this->assertEquals(null, $this->sut->getName());
		$this->sut->setName('bar');
		$this->assertEquals('bar', $this->sut->getName());
	}
}