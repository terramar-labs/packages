<?php

/*
 * Copyright (c) Terramar Labs
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Terramar\Packages\Plugin\GitLab;

use Doctrine\ORM\EntityManager;
use Gitlab\Client;
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
                $package->setHookExternalId('');
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
        $config = $this->getConfig($package);
        if ($config->isEnabled()) {
            return true;
        }
        
        $client = $this->getClient($package->getRemote());
        $project = Project::fromArray($client, (array) $client->api('projects')->show($package->getExternalId()));
        $hook = $project->addHook($this->urlGenerator->generate('webhook_receive', array('id' => $package->getId()), array('push_events' => true, 'tag_push_events' => true)));
        $package->setHookExternalId($hook->id);
        $config->setEnabled(true);

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
        $config = $this->getConfig($package);
        if (!$config->isEnabled()) {
            return true;
        }

        if ($package->getHookExternalId()) {
            $client = $this->getClient($package->getRemote());
            $project = Project::fromArray($client, (array) $client->api('projects')->show($package->getExternalId()));
            $project->removeHook($package->getHookExternalId());
        }

        $package->setHookExternalId('');
        $config->setEnabled(false);

        return true;
    }
    
    private function getConfig(Package $package)
    {
        return $this->entityManager->getRepository('Terramar\Packages\Plugin\GitLab\PackageConfiguration')->findOneBy(array('package' => $package));
    }

    /**
     * @param Remote $remote
     * @return RemoteConfiguration
     */
    private function getRemoteConfig(Remote $remote)
    {
        return $this->entityManager->getRepository('Terramar\Packages\Plugin\GitLab\RemoteConfiguration')->findOneBy(array('remote' => $remote));
    }

    private function getAllProjects(Remote $remote)
    {
        $client = $this->getClient($remote);

        $projects = array();
        $page = 1;
        while (true) {
            $projects = array_merge($projects, $client->api('projects')->accessible($page, 100));
            $linkHeader = $client->getHttpClient()->getLastResponse()->getHeader('Link');
            if (strpos($linkHeader, 'rel="next"') === false) {
                break;
            }

            $page++;
        }

        return $projects;
    }
    
    private function getClient(Remote $remote)
    {
        $config = $this->getRemoteConfig($remote);

        $client = new Client(rtrim($config->getUrl(), '/') . '/api/v3/');
        $client->authenticate($config->getToken(), Client::AUTH_HTTP_TOKEN);

        return $client;
    }

    private function packageExists($existingPackages, $gitlabId)
    {
        return count(array_filter($existingPackages, function(Package $package) use ($gitlabId) {
                    return (string) $package->getExternalId() === (string) $gitlabId;
                })) > 0;
    }
}
