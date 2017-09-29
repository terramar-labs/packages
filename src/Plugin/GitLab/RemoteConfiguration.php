<?php

/*
 * Copyright (c) Terramar Labs
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Terramar\Packages\Plugin\GitLab;

use Doctrine\ORM\Mapping as ORM;
use Terramar\Packages\Entity\Remote;

/**
 * @ORM\Entity
 * @ORM\Table(name="packages_gitlab_remotes")
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
     * @ORM\Column(name="url", type="string")
     */
    private $url;

    /**
     * @ORM\ManyToOne(targetEntity="Terramar\Packages\Entity\Remote")
     * @ORM\JoinColumn(name="remote_id", referencedColumnName="id")
     */
    private $remote;

    /**
     * @ORM\Column(name="fqn_prefix", type="string", nullable=true)
     */
    private $fqn_prefix;

    /**
     * @param mixed $enabled
     */
    public function setEnabled($enabled)
    {
        $this->enabled = (bool) $enabled;
    }

    /**
     * @return bool
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
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @param mixed $url
     */
    public function setUrl($url)
    {
        $this->url = (string) $url;
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

    /**
     * @return mixed
     */
    public function getFqnPrefix()
    {
        return $this->fqn_prefix;
    }

    /**
     * @param mixed $prefix
     */
    public function setFqnPrefix($prefix)
    {
        $this->fqn_prefix = (string) $prefix;
    }
}
