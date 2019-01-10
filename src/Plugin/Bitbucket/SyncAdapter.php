<?php

/*
 * Copyright (c) Terramar Labs
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Terramar\Packages\Plugin\Bitbucket;

use Bitbucket\API\Authentication\Basic;
use Bitbucket\API\Http\Response\Pager;
use Bitbucket\API\Repositories;
use Bitbucket\API\Repositories\Hooks;
use Doctrine\ORM\EntityManager;
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
        return 'Bitbucket';
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
            $package = $this->getExistingPackage($existingPackages, $project['uuid']);
            if ($package === null) {
                $package = new Package();
                $package->setExternalId($project['uuid']);
                $package->setRemote($remote);
            }
            $package->setName($project['name']);
            $package->setDescription($project['description']);
            $package->setFqn($project['full_name']);
            $package->setWebUrl($this->fullNameToWebUrl($project['full_name']));
            $package->setSshUrl($this->fullNameToSshUrl($project['full_name']));
            $packages[] = $package;
        }

        $removed = array_diff($existingPackages, $packages);
        foreach ($removed as $package) {
            $this->entityManager->remove($package);
        }

        return $packages;
    }

    /**
     * @param $fullName string
     *
     * @return string
     */
    private function fullNameToWebUrl($fullName)
    {
        return "https://bitbucket.org/$fullName";
    }

    /**
     * @param $fullName string
     *
     * @return string
     */
    private function fullNameToSshUrl($fullName)
    {
        return "git@bitbucket.org:$fullName.git";
    }

    private function getAllProjects(Remote $remote)
    {
        $auth = $this->getAuth($remote);

        $repositories = new Repositories();
        $repositories->setCredentials($auth);

        $config = $this->getRemoteConfig($remote);
        $account = $config->getAccount();

        $page = new Pager($repositories->getClient(), $repositories->all($account));

        $response = $page->getCurrent();

        $projects = [];
        $pageN = 1;
        while (true) {
            $current = json_decode($response->getContent(), true)['values'];
            $projects = array_merge($projects, $current);
            if (!$page->hasNext()) {
                break;
            }
            $response = $page->fetchNext();

            ++$pageN;
        }

        return $projects;
    }

    private function getAuth(Remote $remote)
    {
        $config = $this->getRemoteConfig($remote);

        return new Basic($config->getUsername(), $config->getToken());
    }

    /**
     * @param Remote $remote
     *
     * @return RemoteConfiguration
     */
    private function getRemoteConfig(Remote $remote)
    {
        return $this->entityManager->getRepository('Terramar\Packages\Plugin\Bitbucket\RemoteConfiguration')->findOneBy(['remote' => $remote]);
    }

    /**
     * @param $existingPackages []Package
     * @param $gitlabId
     *
     * @return Package|null
     */
    private function getExistingPackage($existingPackages, $gitlabId)
    {
        $res = array_filter($existingPackages, function (Package $package) use ($gitlabId) {
            return (string)$package->getExternalId() === (string)$gitlabId;
        });
        if (count($res) === 0) {
            return null;
        }

        return array_shift($res);
    }


    /**
     * Enable a Bitbucket webhook for the given Package.
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
            $auth = $this->getAuth($package->getRemote());

            $Hook = new Hooks();
            $Hook->setCredentials($auth);

            $fqn = $config->getPackage()->getFqn();
            $fqnArr = explode('/', $fqn);
            $account = array_shift($fqnArr);
            $repoName = implode('/', $fqnArr);
            $hookUrl = $this->urlGenerator->generate('webhook_receive', ['id' => $package->getId()], true);

            $response = $Hook->create($account, $repoName, [
                'description' => 'Rebuild on Packages',
                'url' => $hookUrl,
                'active' => true,
                'events' => ['repo:push'],
            ]);


            $hook = json_decode($response->getContent(), true);

            if (!empty($hook['uuid'])) {
                $package->setHookExternalId($hook['uuid']);
                $config->setEnabled(true);
            }

            return true;

        } catch (\Exception $e) {
            // TODO: Log the exception
            return false;
        }
    }

    private function getConfig(Package $package)
    {
        return $this->entityManager->getRepository('Terramar\Packages\Plugin\Bitbucket\PackageConfiguration')->findOneBy(['package' => $package]);
    }

    /**
     * Disable a Bitbucket webhook for the given Package.
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
            if ($uuid = $package->getHookExternalId()) {
                $auth = $this->getAuth($package->getRemote());
                $Hook = new Hooks();
                $Hook->setCredentials($auth);


                $fqn = $config->getPackage()->getFqn();
                $fqnArr = explode('/', $fqn);
                $account = array_shift($fqnArr);
                $repoName = implode('/', $fqnArr);


                $resp = $Hook->delete($account, $repoName, $uuid);
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
