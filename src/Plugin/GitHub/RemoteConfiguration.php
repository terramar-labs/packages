<?php

/*
 * Copyright (c) Terramar Labs
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Terramar\Packages\Plugin\GitHub;

use Doctrine\ORM\Mapping as ORM;
use Terramar\Packages\Entity\Package;
use Terramar\Packages\Entity\Remote;

/**
 * @ORM\Entity
 * @ORM\Table(name="packages_github_remotes")
 */
class RemoteConfiguration
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
     * @ORM\Column(name="token", type="string")
     */
    private $token;

    /**
     * @ORM\Column(name="username", type="string")
     */
    private $username;
    
    /**
     * @ORM\ManyToOne(targetEntity="Terramar\Packages\Entity\Remote")
     * @ORM\JoinColumn(name="remote_id", referencedColumnName="id")
     */
    private $remote;

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
     * @return Remote
     */
    public function getRemote()
    {
        return $this->remote;
    }

    /**
     * @param Remote $remote
     */
    public function setRemote(Remote $remote)
    {
        $this->remote = $remote;
    }

    /**
     * @return mixed
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * @param mixed $username
     */
    public function setUsername($username)
    {
        $this->username = (string) $username;
    }

    /**
     * @return mixed
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * @param mixed $token
     */
    public function setToken($token)
    {
        $this->token = (string) $token;
    }
}
