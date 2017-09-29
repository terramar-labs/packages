<?php

/*
 * Copyright (c) Terramar Labs
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Terramar\Packages\Plugin;

/**
 * Actions defines constants for the various actions available to plugins.
 *
 * @see http://docs.terramarlabs.com/packages/3.1/plugins/action-reference
 */
final class Actions
{
    /**
     * This action should render a single dashboard widget.
     */
    const DASHBOARD_INDEX = 'dashboard.index';

    /**
     * This action should render a form to configure a new remote.
     */
    const REMOTE_NEW = 'remote.new';

    /**
     * This action is dispatched when a remote is created.
     */
    const REMOTE_CREATE = 'remote.create';

    /**
     * This action should render a form to configure a existing remote.
     */
    const REMOTE_EDIT = 'remote.edit';

    /**
     * This action is dispatched when a remote is updated.
     */
    const REMOTE_UPDATE = 'remote.update';

    /**
     * This action should render a form to configure a existing package.
     */
    const PACKAGE_EDIT = 'package.edit';

    /**
     * This action is dispatched when a package is updated.
     */
    const PACKAGE_UPDATE = 'package.update';
}
