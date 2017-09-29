<?php

/*
 * Copyright (c) Terramar Labs
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Terramar\Packages\Plugin\Satis;

use Doctrine\ORM\EntityManager;
use Symfony\Component\Filesystem\Filesystem;
use Terramar\Packages\Entity\Package;

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
     * @param string $rootDir
     * @param string $cacheDir
     * @param array $config
     */
    public function __construct(EntityManager $entityManager, $rootDir, $cacheDir, array $config)
    {
        $this->entityManager = $entityManager;
        $this->filesystem = new Filesystem();
        $this->rootDir = $rootDir;
        $this->cacheDir = $cacheDir;
        $this->config = $config;
    }

    public function generateConfiguration(array $options = array())
    {
        $data = array_merge($options, array(
            'name' => $this->config['name'],
            'homepage' => $this->config['homepage'],
            'output-dir' => realpath($this->config['output_dir']),
            'repositories' => array(),
            'output-html' => false,
            'require-dependencies' => true,
            'require-dev-dependencies' => true,
        ));

        $packages = $this->entityManager->getRepository('Terramar\Packages\Plugin\Satis\PackageConfiguration')
            ->createQueryBuilder('pc')
            ->join('pc.package', 'p')
            ->where('pc.enabled = true')
            ->andWhere('p.enabled = true')
            ->getQuery()
            ->getResult();

        $repositories = array_map(function (PackageConfiguration $config) {
                return $config->getPackage()->getSshUrl();
            }, $packages);

        foreach ($repositories as $repository) {
            $data['repositories'][] = array(
                'type' => 'vcs',
                'url' => $repository,
            );
        }

        $this->filesystem->mkdir($this->cacheDir.'/satis');

        $filename = tempnam($this->cacheDir.'/satis', 'satis_');

        $this->filesystem->dumpFile($filename, json_encode($data, JSON_PRETTY_PRINT));

        return $filename;
    }
}
