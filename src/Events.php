<?php

/*
 * Copyright (c) Terramar Labs
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Terramar\Packages;

/**
 * Events defines constants describing the possible events to subscribe to.
 *
 * @see http://docs.terramarlabs.com/packages/3.2/plugins/event-reference
 */
final class Events
{
    /**
     * Dispatched when a package is created for a project.
     */
    const PACKAGE_CREATE = 'package.create';

    /**
     * Dispatched when a package's source code is updated.
     */
    const PACKAGE_UPDATE = 'package.update';

    /**
     * Dispatched when a package is enabled.
     */
    const PACKAGE_ENABLE = 'package.enable';

    /**
     * Dispatched when a package is disabled.
     */
    const PACKAGE_DISABLE = 'package.disable';

    /**
     * Dispatched when a remote is enabled.
     */
    const REMOTE_ENABLE = 'remote.enable';

    /**
     * Dispatched when a remote is disabled.
     */
    const REMOTE_DISABLE = 'remote.disable';
}
