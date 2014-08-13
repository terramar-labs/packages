<?php

namespace Terramar\Packages\Plugin\Git;

use Doctrine\ORM\EntityManager;
use Gitlab\Client;
use Gitlab\Model\Project;
use Nice\Router\UrlGeneratorInterface;
use PHPGit\Git;
use Terramar\Packages\Entity\Remote;
use Terramar\Packages\Entity\Package;
use Terramar\Packages\Helper\SyncAdapterInterface;

class SyncAdapter implements SyncAdapterInterface
{
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    private $entityManager;

    /**
     * @var \Nice\Router\UrlGeneratorInterface
     */
    private $urlGenerator;
    /**
     * @var \PHPGit\Git
     */
    private $git;
    /**
     * @var
     */
    private $cacheDir;

    /**
     * Constructor
     * 
     * @param EntityManager         $entityManager
     * @param UrlGeneratorInterface $urlGenerator
     */
    public function __construct(EntityManager $entityManager, UrlGeneratorInterface $urlGenerator, Git $git, $cacheDir)
    {
        $this->entityManager = $entityManager;
        $this->urlGenerator = $urlGenerator;
        $this->git = $git;
        $this->cacheDir = $cacheDir . '/git';
    }

    /**
     * @param Remote $remote
     *
     * @return bool
     */
    public function supports(Remote $remote)
    {
        return $remote->getAdapter() === $this->getName();
    }

    /**
     * @param Remote $remote
     *
     * @return Package[]
     */
    public function synchronizePackages(Remote $remote)
    {
        $existingPackage = $this->entityManager->getRepository('Terramar\Packages\Entity\Package')->findOneBy(array('remote' => $remote));

        $packages = array();
        if (!$existingPackage) {
            $project = $this->getProject($remote);

            $package = new Package();
            $package->setExternalId($project['id']);
            $package->setName($project['name']);
            $package->setFqn($project['path_with_namespace']);
            $package->setSshUrl($project['ssh_url']);
            $package->setRemote($remote);

            $packages[] = $package;
        }
        
        return $packages;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'Git';
    }

    /**
     * Enable a git post-receive-hook for the given Package
     * 
     * @param Package $package
     *
     * @return bool
     */
    public function enableHook(Package $package)
    {
        if ($package->isEnabled()) {
            return true;
        }
        
        $package->setEnabled(true);

        return true;
    }

    /**
     * Disable a git post-receive-hook for the given Package
     * 
     * @param Package $package
     *
     * @return bool
     */
    public function disableHook(Package $package)
    {
        if (!$package->isEnabled()) {
            return true;
        }

        return true;
    }

    private function getProject(Remote $remote)
    {
        $url  = $remote->getUrl();
        $name = ltrim(parse_url($url, PHP_URL_PATH), '/\\');
        
        $project = array();
        $project['id'] = $project['ssh_url'] = $remote->getUrl();
        $project['name'] = $project['path_with_namespace'] = $name;
        
        return $project;
    }
}