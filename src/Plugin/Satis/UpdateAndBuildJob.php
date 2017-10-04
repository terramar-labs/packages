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
use Symfony\Component\Process\PhpExecutableFinder;
use Symfony\Component\Process\ProcessBuilder;
use Terramar\Packages\Entity\Package;
use Terramar\Packages\Job\ContainerAwareJob;

class UpdateAndBuildJob extends ContainerAwareJob
{
    /**
     * @return string
     */
    private function getCacheDir(Package $package)
    {
        return $this->getContainer()->getParameter('app.cache_dir') . '/auth/' . $package->getFqn();
    }

    /**
     * @return EntityManager
     */
    private function getEntityManager()
    {
        return $this->getContainer()->get('doctrine.orm.entity_manager');
    }

    public function run($args)
    {
        /** @var Package $package */
        $package = $this->getEntityManager()->getRepository(Package::class)->find($args['package_id']);

        $config = [];
        $remote = $package->getRemote();
        switch ($remote->getAdapter()) {
            case 'GitHub':
                /** @var \Terramar\Packages\Plugin\GitHub\RemoteConfiguration $remoteConfig */
                $remoteConfig = $this->getEntityManager()->getRepository('Terramar\Packages\Plugin\GitHub\RemoteConfiguration')
                    ->findOneBy(['remote' => $remote]);
                if ( ! $remoteConfig) {
                    throw new \RuntimeException('Unable to find RemoteConfiguration for ' . $remote->getAdapter() . ' ' . $remote->getName());
                }

                $config['github-oauth']['github.com'] = $remoteConfig->getToken();

                break;

            case 'GitLab':
                /** @var \Terramar\Packages\Plugin\GitLab\RemoteConfiguration $remoteConfig */
                $remoteConfig = $this->getEntityManager()->getRepository('Terramar\Packages\Plugin\GitLab\RemoteConfiguration')
                    ->findOneBy(['remote' => $remote]);
                if ( ! $remoteConfig) {
                    throw new \RuntimeException('Unable to find RemoteConfiguration for ' . $remote->getAdapter() . ' ' . $remote->getName());
                }

                $config['gitlab-token']['gitlab.com'] = $remoteConfig->getToken();

                break;
        }

        $finder = new PhpExecutableFinder();
        $builder = new ProcessBuilder([
            'bin/console',
            'satis:build',
            '--repository-url',
            $package->getSshUrl(),
        ]);

        $filesystem = new Filesystem();

        $filesystem->mkdir($this->getCacheDir($package));

        $filename = $this->getCacheDir($package) . '/auth.json';

        $filesystem->dumpFile($filename, json_encode($config, JSON_PRETTY_PRINT));

        $builder->setEnv('HOME', $this->getContainer()->getParameter('app.root_dir'));
        $builder->setEnv('COMPOSER_HOME', dirname($filename));
        $builder->setPrefix($finder->find());
        $builder->setTimeout(null);

        echo $builder->getProcess()->getCommandLine() . "\n";

        $process = $builder->getProcess();
        $process->run(function ($type, $message) {
            echo $message;
        });
    }
}
