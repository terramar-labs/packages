<?php

namespace Terramar\Packages\Plugin\GitLab;

use Doctrine\ORM\EntityManager;
use Gitlab\Model\Project;
use Nice\Router\UrlGeneratorInterface;
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
     * Constructor
     * 
     * @param EntityManager         $entityManager
     * @param UrlGeneratorInterface $urlGenerator
     */
    public function __construct(EntityManager $entityManager, UrlGeneratorInterface $urlGenerator)
    {
        $this->entityManager = $entityManager;
        $this->urlGenerator = $urlGenerator;
    }

    /**
     * @param Remote $configuration
     *
     * @return bool
     */
    public function supports(Remote $configuration)
    {
        return true;
    }

    /**
     * @param Remote $remote
     *
     * @return Package[]
     */
    public function synchronizePackages(Remote $remote)
    {
        $existingPackages = $this->entityManager->getRepository('Terramar\Packages\Entity\Package')->findBy(array('remote' => $remote));

        $projects = $this->getAllProjects($remote);

        $packages = array();
        foreach ($projects as $project) {
            if (!$this->packageExists($existingPackages, $project['id'])) {
                $package = new Package();
                $package->setExternalId($project['id']);
                $package->setName($project['name']);
                $package->setDescription($project['description']);
                $package->setFqn($project['path_with_namespace']);
                $package->setWebUrl($project['web_url']);
                $package->setSshUrl($project['ssh_url_to_repo']);
                $package->setRemote($remote);

                $packages[] = $package;
            }
        }

        return $packages;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'GitLab';
    }

    /**
     * Enable a GitLab webhook for the given Package
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

        $client = $package->getRemote()->createClient();
        $project = Project::fromArray($client, (array) $client->api('projects')->show($package->getExternalId()));
        $hook = $project->addHook($this->urlGenerator->generate('webhook_receive', array('id' => $package->getId()), true));
        $package->setHookExternalId($hook->id);
        $package->setEnabled(true);

        return true;
    }

    /**
     * Disable a GitLab webhook for the given Package
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

        if ($package->getHookExternalId()) {
            $client  = $package->getRemote()->createClient();
            $project = Project::fromArray($client, (array) $client->api('projects')->show($package->getExternalId()));
            $project->removeHook($package->getHookExternalId());
        }

        $package->setHookExternalId('');
        $package->setEnabled(false);

        return true;
    }

    private function getAllProjects(Remote $configuration)
    {
        $client = $configuration->createClient();

        $projects = array();
        $page = 1;
        while (true) {
            $projects = array_merge($projects, $client->api('projects')->all($page, 100));
            $linkHeader = $client->getHttpClient()->getLastResponse()->getHeader('Link');
            if (strpos($linkHeader, 'rel="next"') === false) {
                break;
            }

            $page++;
        }

        return $projects;
    }

    private function packageExists($existingPackages, $gitlabId)
    {
        return count(array_filter($existingPackages, function(Package $package) use ($gitlabId) {
                    return (string) $package->getExternalId() === (string) $gitlabId;
                })) > 0;
    }
}