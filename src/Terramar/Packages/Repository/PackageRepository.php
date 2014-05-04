<?php

namespace Terramar\Packages\Repository;

use Doctrine\Common\Cache\Cache;
use Terramar\Packages\Model\Package;

class PackageRepository
{
    /**
     * @var \Doctrine\Common\Cache\Cache
     */
    private $cache;

    /**
     * Constructor
     * 
     * @param Cache $cache
     */
    public function __construct(Cache $cache)
    {
        $this->cache = $cache;
    }
    
    public function findAll()
    {
        $packages = $this->cache->fetch('packages:index') ?: array();
        
        return $packages;
    }
    
    public function findById($id)
    {
        return $this->cache->fetch('package:' . $id);
    }
    
    public function save(Package $package)
    {
        $indexArray = $this->cache->fetch('packages:index') ?: array();
        if (!$package->getId()) {
            $index = array_push($indexArray, $package);
            $package->setId($index);
        }
        
        $this->cache->save('package:' . $package->getId(), $package);
        $this->cache->save('packages:index', $indexArray);
    }
}