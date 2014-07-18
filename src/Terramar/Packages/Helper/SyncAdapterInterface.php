<?php

namespace Terramar\Packages\Helper;

use Terramar\Packages\Entity\Configuration;
use Terramar\Packages\Entity\Package;

interface SyncAdapterInterface
{
    /**
     * Returns true if the adapter supports the given configuration
     * 
     * @param Configuration $configuration
     *
     * @return bool
     */
    public function supports(Configuration $configuration);

    /**
     * Synchronizes the given adapter, returning any new Packages
     * 
     * @param Configuration $configuration
     *
     * @return Package[]
     */
    public function synchronizePackages(Configuration $configuration);
}