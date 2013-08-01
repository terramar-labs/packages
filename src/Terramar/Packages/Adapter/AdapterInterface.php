<?php

namespace Terramar\Packages\Adapter;

/**
 * Defines the contract any repository adapter must follow
 */
interface AdapterInterface
{
    /**
     * Gets a list of repositories
     *
     * @return array
     */
    public function getRepositories();
}
