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
        $names = $this->cache->fetch('name_index') ?: array();
        $packages = array();
        foreach ($names as $name) {
            $packages[] = $this->findByName($name);
        }
        
        return $packages;
    }
    
    public function findByName($name)
    {
        return $this->cache->fetch($name);
    }
    
    public function save(Package $package)
    {
        $this->cache->save($package->getName(), $package);
        $nameIndex = $this->cache->fetch('name_index');
        $nameIndex[] = $package->getName();
        $this->cache->save('name_index', $nameIndex);
    }
}