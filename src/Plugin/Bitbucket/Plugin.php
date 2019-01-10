<?php

/*
 * Copyright (c) Terramar Labs
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Terramar\Packages\Plugin\Bitbucket;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Terramar\Packages\Plugin\Actions;
use Terramar\Packages\Plugin\PluginInterface;

class Plugin implements PluginInterface {
	/**
	 * Configure the given ContainerBuilder.
	 *
	 * This method allows a plugin to register additional services with the
	 * service container.
	 *
	 * @param ContainerBuilder $container
	 */
	public function configure( ContainerBuilder $container ) {
		$container->register( 'packages.plugin.bitbucket.adapter', 'Terramar\Packages\Plugin\Bitbucket\SyncAdapter' )
		          ->addArgument( new Reference( 'doctrine.orm.entity_manager' ) )
		          ->addArgument( new Reference( 'router.url_generator' ) );

		$container->getDefinition( 'packages.helper.sync' )
		          ->addMethodCall( 'registerAdapter', [ new Reference( 'packages.plugin.Bitbucket.adapter' ) ] );

		$container->register( 'packages.plugin.bitbucket.package_subscriber',
			'Terramar\Packages\Plugin\Bitbucket\PackageSubscriber' )
		          ->addArgument( new Reference( 'packages.plugin.bitbucket.adapter' ) )
		          ->addArgument( new Reference( 'doctrine.orm.entity_manager' ) )
		          ->addTag( 'kernel.event_subscriber' );

		$container->register( 'packages.plugin.bitbucket.remote_subscriber',
			'Terramar\Packages\Plugin\Bitbucket\RemoteSubscriber' )
		          ->addArgument( new Reference( 'packages.plugin.bitbucket.adapter' ) )
		          ->addArgument( new Reference( 'doctrine.orm.entity_manager' ) )
		          ->addTag( 'kernel.event_subscriber' );

		$container->getDefinition( 'packages.controller_manager' )
		          ->addMethodCall( 'registerController',
			          [ Actions::REMOTE_NEW, 'Terramar\Packages\Plugin\Bitbucket\Controller::newAction' ] )
		          ->addMethodCall( 'registerController',
			          [ Actions::REMOTE_CREATE, 'Terramar\Packages\Plugin\Bitbucket\Controller::createAction' ] )
		          ->addMethodCall( 'registerController',
			          [ Actions::REMOTE_EDIT, 'Terramar\Packages\Plugin\Bitbucket\Controller::editAction' ] )
		          ->addMethodCall( 'registerController',
			          [ Actions::REMOTE_UPDATE, 'Terramar\Packages\Plugin\Bitbucket\Controller::updateAction' ] );
	}

	/**
	 * Get the plugin name.
	 *
	 * @return string
	 */
	public function getName() {
		return 'Bitbucket';
	}

	/**
	 */
	public function getVersion() {
		return;
	}
}
