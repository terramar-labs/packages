<?php

namespace Terramar\Packages\Plugin;

final class Actions
{
    /**
     * This action should render a single dashboard widget
     */
    const DASHBOARD_INDEX = 'dashboard.index';
    
    /**
     * This action should render a form to configure a new remote
     */
    const REMOTE_NEW = 'remote.new';
    
    /**
     * This action is dispatched when a remote is created
     */
    const REMOTE_CREATE = 'remote.create';

    /**
     * This action should render a form to configure a existing remote
     */
    const REMOTE_EDIT = 'remote.edit';

    /**
     * This action is dispatched when a remote is updated
     */
    const REMOTE_UPDATE = 'remote.update';

    /**
     * This action should render a form to configure a existing package
     */
    const PACKAGE_EDIT = 'package.edit';

    /**
     * This action is dispatched when a package is updated
     */
    const PACKAGE_UPDATE = 'package.update';
}