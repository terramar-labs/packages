<?php

namespace Terramar\Packages\Plugin\GitLab;

use Doctrine\ORM\Mapping as ORM;
use Terramar\Packages\Entity\Package;

/**
 * @ORM\Entity
 * @ORM\Table(name="packages_gitlab_configurations")
 */
class PackageConfiguration
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(name="id", type="integer")
     */
    private $id;

    /**
     * @ORM\Column(name="enabled", type="boolean")
     */
    private $enabled = false;
    
    /**
     * @ORM\ManyToOne(targetEntity="Terramar\Packages\Entity\Package")
     * @ORM\JoinColumn(name="package_id", referencedColumnName="id")
     */
    private $package;

    /**
     * @param mixed $enabled
     */
    public function setEnabled($enabled)
    {
        $this->enabled = (bool) $enabled;
    }

    /**
     * @return boolean
     */
    public function isEnabled()
    {
        return $this->enabled;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return Package
     */
    public function getPackage()
    {
        return $this->package;
    }

    /**
     * @param Package $package
     */
    public function setPackage(Package $package)
    {
        $this->package = $package;
    }
}