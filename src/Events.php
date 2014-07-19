<?php

namespace Terramar\Packages;

final class Events
{
    /**
     * Dispatched when a package is created for a project
     */
    const PACKAGE_CREATE = 'package.create';
    
    /**
     * Dispatched when a package's source code is updated
     */
    const PACKAGE_UPDATE = 'package.update';

    /**
     * Dispatched when a package is enabled
     */
    const PACKAGE_ENABLE = 'package.enable';

    /**
     * Dispatched when a package is disabled
     */
    const PACKAGE_DISABLE = 'package.disable';
}