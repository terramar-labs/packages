<?php

namespace Terramar\Packages\Plugin\GitHub;

use Doctrine\ORM\EntityManager;
use Github\Client;
use Github\HttpClient\Message\ResponseMediator;
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
                $package->setFqn($project['full_name']);
                $package->setWebUrl($project['clone_url']);
                $package->setSshUrl($project['ssh_url']);
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
        return 'GitHub';
    }

    /**
     * Enable a GitHub webhook for the given Package
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
        $url = 'repos/'.$package->getFqn().'/hooks';
        $response = $client->getHttpClient()->post($url, json_encode(array(
            'name' => 'web',
            'config' => array(
                'url' => $this->urlGenerator->generate('webhook_receive', array('id' => $package->getId()), true)
            )
        )));

        $hook = ResponseMediator::getContent($response);

        $package->setHookExternalId($hook['id']);
        $config->setEnabled(true);

        return true;
    }

    /**
     * Disable a GitHub webhook for the given Package
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
            $url = 'repos/'.$package->getFqn().'/hooks/' . $package->getHookExternalId();
            $client->getHttpClient()->delete($url);
        }

        $package->setHookExternalId('');
        $config->setEnabled(false);

        return true;
    }
    
    private function getConfig(Package $package)
    {
        return $this->entityManager->getRepository('Terramar\Packages\Plugin\GitHub\PackageConfiguration')->findOneBy(array('package' => $package));
    }

    private function getAllProjects(Remote $remote)
    {
        $client = $this->getClient($remote);

        $user = basename($remote->getUrl());

        $projects = $client->api('user')->repositories($user);

        return $projects;
    }
    
    private function getClient(Remote $remote)
    {
        $client = new Client();
        $client->authenticate($remote->getToken(), Client::AUTH_HTTP_TOKEN);

        return $client;
    }

    private function packageExists($existingPackages, $githubId)
    {
        return count(array_filter($existingPackages, function(Package $package) use ($githubId) {
                    return (string) $package->getExternalId() === (string) $githubId;
                })) > 0;
    }
}
