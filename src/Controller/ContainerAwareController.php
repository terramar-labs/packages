<?php namespace Terramar\Packages\Controller;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

abstract class ContainerAwareController implements ContainerAwareInterface
{
	/**
	 * @var ContainerInterface|null
	 */
	protected $container;

	/**
	 * {@inheritdoc}
	 */
	public function setContainer(ContainerInterface $container = null)
	{
		$this->container = $container;
	}
}