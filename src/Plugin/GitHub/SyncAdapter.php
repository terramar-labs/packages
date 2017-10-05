<?php

/*
 * Copyright (c) Terramar Labs
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Terramar\Packages\Plugin\GitHub;

use Doctrine\ORM\EntityManager;
use Github\Client;
use Github\HttpClient\Message\ResponseMediator;
use Nice\Router\UrlGeneratorInterface;
use Terramar\Packages\Entity\Package;
use Terramar\Packages\Entity\Remote;
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
     * Constructor.
     *
     * @param EntityManager $entityManager
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
     * @return string
     */
    public function getName()
    {
        return 'GitHub';
    }

    /**
     * @param Remote $remote
     *
     * @return Package[]
     */
    public function synchronizePackages(Remote $remote)
    {
        $existingPackages = $this->entityManager->getRepository('Terramar\Packages\Entity\Package')->findBy(['remote' => $remote]);

        $projects = $this->getAllProjects($remote);

        $packages = [];
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

    private function getAllProjects(Remote $remote)
    {
        $client = $this->getClient($remote);

        $projects = [];
        $page = 1;
        while (true) {
            $response = $client->getHttpClient()->get('/user/repos', [
                'page'     => $page,
                'per_page' => 100,
            ]);
            $projects = array_merge($projects, ResponseMediator::getContent($response));
            $pageInfo = ResponseMediator::getPagination($response);
            if (!isset($pageInfo['next'])) {
                break;
            }

            ++$page;
        }

        return $projects;
    }

    private function getClient(Remote $remote)
    {
        $config = $this->getRemoteConfig($remote);

        $client = new Client();
        $client->authenticate($config->getToken(), Client::AUTH_HTTP_TOKEN);

        return $client;
    }

    /**
     * @param Remote $remote
     *
     * @return RemoteConfiguration
     */
    private function getRemoteConfig(Remote $remote)
    {
        return $this->entityManager->getRepository('Terramar\Packages\Plugin\GitHub\RemoteConfiguration')->findOneBy(['remote' => $remote]);
    }

    private function packageExists($existingPackages, $githubId)
    {
        return count(array_filter($existingPackages, function (Package $package) use ($githubId) {
                return (string)$package->getExternalId() === (string)$githubId;
            })) > 0;
    }

    /**
     * Enable a GitHub webhook for the given Package.
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

        try {
            $client = $this->getClient($package->getRemote());
            $url = 'repos/' . $package->getFqn() . '/hooks';
            $response = $client->getHttpClient()->post($url, json_encode([
                'name'   => 'web',
                'config' => [
                    'url'          => $this->urlGenerator->generate('webhook_receive', ['id' => $package->getId()],
                        true),
                    'content_type' => 'json',
                ],
                'events' => ['push', 'create'],
            ]));

            $hook = ResponseMediator::getContent($response);

            $package->setHookExternalId($hook['id']);
            $config->setEnabled(true);

            return true;

        } catch (\Exception $e) {
            // TODO: Log the exception
            return false;
        }
    }

    private function getConfig(Package $package)
    {
        return $this->entityManager->getRepository('Terramar\Packages\Plugin\GitHub\PackageConfiguration')->findOneBy(['package' => $package]);
    }

    /**
     * Disable a GitHub webhook for the given Package.
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

        try {
            if ($package->getHookExternalId()) {
                $client = $this->getClient($package->getRemote());
                $url = 'repos/' . $package->getFqn() . '/hooks/' . $package->getHookExternalId();
                $client->getHttpClient()->delete($url);
            }

            $package->setHookExternalId('');
            $config->setEnabled(false);

            return true;

        } catch (\Exception $e) {
            // TODO: Log the exception
            $package->setHookExternalId('');
            $config->setEnabled(false);

            return false;
        }
    }
}
