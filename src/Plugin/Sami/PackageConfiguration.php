<?php

/*
 * Copyright (c) Terramar Labs
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Terramar\Packages\Plugin\Sami;

use Doctrine\ORM\Mapping as ORM;
use Terramar\Packages\Entity\Package;

/**
 * @ORM\Entity
 * @ORM\Table(name="packages_sami_configurations")
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
     * @ORM\Column(name="repo_path", type="string")
     */
    private $repositoryPath = '';

    /**
     * @ORM\Column(name="remote_repo_path", type="string")
     */
    private $remoteRepoPath = '';

    /**
     * @ORM\Column(name="docs_path", type="string")
     */
    private $docsPath = 'src/';

    /**
     * @ORM\Column(name="title", type="string")
     */
    private $title = '';

    /**
     * @ORM\Column(name="theme", type="string")
     */
    private $theme = 'symfony';

    /**
     * @ORM\Column(name="tags", type="string")
     */
    private $tags = '';

    /**
     * @ORM\Column(name="refs", type="string")
     */
    private $refs = '';

    /**
     * @ORM\Column(name="templates_dir", type="string")
     */
    private $templatesDir = '';

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
        $this->enabled = (bool)$enabled;
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

    /**
     * @return mixed
     */
    public function getRepositoryPath()
    {
        return $this->repositoryPath;
    }

    /**
     * @param mixed $repositoryPath
     */
    public function setRepositoryPath($repositoryPath)
    {
        $this->repositoryPath = (string)$repositoryPath;
    }

    /**
     * @return mixed
     */
    public function getRemoteRepoPath()
    {
        return $this->remoteRepoPath;
    }

    /**
     * @param mixed $remoteRepoPath
     */
    public function setRemoteRepoPath($remoteRepoPath)
    {
        $this->remoteRepoPath = (string)$remoteRepoPath;
    }

    /**
     * @return mixed
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param mixed $title
     */
    public function setTitle($title)
    {
        $this->title = (string)$title;
    }

    /**
     * @return mixed
     */
    public function getTheme()
    {
        return $this->theme;
    }

    /**
     * @param mixed $theme
     */
    public function setTheme($theme)
    {
        $this->theme = (string)$theme;
    }

    /**
     * @return mixed
     */
    public function getTags()
    {
        return $this->tags;
    }

    /**
     * @param mixed $tags
     */
    public function setTags($tags)
    {
        $this->tags = (string)$tags;
    }

    /**
     * @return mixed
     */
    public function getRefs()
    {
        return $this->refs;
    }

    /**
     * @param mixed $refs
     */
    public function setRefs($refs)
    {
        $this->refs = (string)$refs;
    }

    /**
     * @return mixed
     */
    public function getDocsPath()
    {
        return $this->docsPath;
    }

    /**
     * @param mixed $docsPath
     */
    public function setDocsPath($docsPath)
    {
        $this->docsPath = (string)$docsPath;
    }

    /**
     * @return mixed
     */
    public function getTemplatesDir()
    {
        return $this->templatesDir;
    }

    /**
     * @param mixed $templatesDir
     */
    public function setTemplatesDir($templatesDir)
    {
        $this->templatesDir = (string)$templatesDir;
    }
}
