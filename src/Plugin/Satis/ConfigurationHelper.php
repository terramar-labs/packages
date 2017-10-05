<?php

/*
 * Copyright (c) Terramar Labs
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Terramar\Packages\Plugin\Satis;

use Doctrine\ORM\EntityManager;
use Nice\Router\UrlGeneratorInterface;
use Symfony\Component\Filesystem\Filesystem;

class ConfigurationHelper
{
    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var \Nice\Router\UrlGeneratorInterface
     */
    private $urlGenerator;

    /**
     * @var string
     */
    private $rootDir;

    /**
     * @var string
     */
    private $cacheDir;

    /**
     * @var array
     */
    private $config;

    /**
     * @param EntityManager $entityManager
     * @param UrlGeneratorInterface $urlGenerator
     * @paramstring $rootDir
     * @param string $cacheDir
     * @param array $config
     */
    public function __construct(
        EntityManager $entityManager,
        UrlGeneratorInterface $urlGenerator,
        $rootDir,
        $cacheDir,
        array $config
    ) {
        $this->entityManager = $entityManager;
        $this->urlGenerator = $urlGenerator;
        $this->filesystem = new Filesystem();
        $this->rootDir = $rootDir;
        $this->cacheDir = $cacheDir;
        $this->config = $config;
    }

    public function generateConfiguration(array $options = [])
    {
        $data = array_merge($options, [
            'name'                     => $this->config['name'],
            'homepage'                 => $this->config['homepage'],
            'output-dir'               => realpath($this->config['output_dir']),
            'repositories'             => [],
            'output-html'              => false,
            'require-dependencies'     => true,
            'require-dev-dependencies' => true,
            'config'                   => [],
        ]);

        if (isset($this->config['archive'])) {
            $data['archive'] = [
                'directory'  => 'dist',
                'format'     => 'tar',
                'prefix-url' => $this->config['base_path'],
                'skip-dev'   => true,
            ];
        }

        $packages = $this->entityManager->getRepository('Terramar\Packages\Plugin\Satis\PackageConfiguration')
            ->createQueryBuilder('pc')
            ->join('pc.package', 'p')
            ->where('pc.enabled = true')
            ->andWhere('p.enabled = true')
            ->getQuery()
            ->getResult();

        $gitlabDomains = [];

        $data['repositories'] = array_map(function (PackageConfiguration $config) use (&$gitlabDomains) {
            $options = [
                'type' => 'vcs',
                'url'  => $config->getPackage()->getSshUrl(),
            ];
            $remote = $config->getPackage()->getRemote();
            switch ($remote->getAdapter()) {
                case 'GitHub':
                    /** @var \Terramar\Packages\Plugin\GitHub\RemoteConfiguration $remoteConfig */
                    $remoteConfig = $this->entityManager->getRepository('Terramar\Packages\Plugin\GitHub\RemoteConfiguration')
                        ->findOneBy(['remote' => $remote]);
                    if (!$remoteConfig) {
                        throw new \RuntimeException('Unable to find RemoteConfiguration for ' . $remote->getAdapter() . ' ' . $remote->getName());
                    }

                    $options['github-token'] = $remoteConfig->getToken();

                    break;

                case 'GitLab':
                    /** @var \Terramar\Packages\Plugin\GitLab\RemoteConfiguration $remoteConfig */
                    $remoteConfig = $this->entityManager->getRepository('Terramar\Packages\Plugin\GitLab\RemoteConfiguration')
                        ->findOneBy(['remote' => $remote]);
                    if (!$remoteConfig) {
                        throw new \RuntimeException('Unable to find RemoteConfiguration for ' . $remote->getAdapter() . ' ' . $remote->getName());
                    }

                    $url = parse_url($remoteConfig->getUrl(), PHP_URL_HOST);
                    if (!in_array($url, $gitlabDomains)) {
                        $gitlabDomains[] = $url;
                    }

                    $options['gitlab-token'] = $remoteConfig->getToken();

                    break;
            }

            return $options;
        }, $packages);

        $data['config']['gitlab-domains'] = $gitlabDomains;

        $this->filesystem->mkdir($this->cacheDir . '/satis');

        $filename = tempnam($this->cacheDir . '/satis', 'satis_');

        $this->filesystem->dumpFile($filename, json_encode($data, JSON_PRETTY_PRINT));

        echo json_encode($data, JSON_PRETTY_PRINT);

        return $filename;
    }
}
