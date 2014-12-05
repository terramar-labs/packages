<?php

/*
 * Copyright (c) Terramar Labs
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Terramar\Packages\Helper;

use Terramar\Packages\Entity\Remote;
use Terramar\Packages\Entity\Package;

interface SyncAdapterInterface
{
    /**
     * Returns true if the adapter supports the given configuration
     * 
     * @param Remote $remote
     *
     * @return bool
     */
    public function supports(Remote $remote);

    /**
     * Synchronizes the given adapter, returning any new Packages
     * 
     * @param Remote $remote
     *
     * @return Package[]
     */
    public function synchronizePackages(Remote $remote);

    /**
     * Gets the name of the adapter
     * 
     * @return string
     */
    public function getName();
}