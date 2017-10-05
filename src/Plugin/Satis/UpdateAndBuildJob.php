<?php

/*
 * Copyright (c) Terramar Labs
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Terramar\Packages\Plugin\Satis;

use Doctrine\ORM\EntityManager;
use Symfony\Component\Process\PhpExecutableFinder;
use Symfony\Component\Process\ProcessBuilder;
use Terramar\Packages\Entity\Package;
use Terramar\Packages\Job\ContainerAwareJob;

class UpdateAndBuildJob extends ContainerAwareJob
{
    public function run($args)
    {
        /** @var Package $package */
        $package = $this->getEntityManager()->getRepository(Package::class)->find($args['package_id']);

        $finder = new PhpExecutableFinder();
        $builder = new ProcessBuilder([
            'bin/console',
            'satis:build',
            '--repository-url',
            $package->getSshUrl(),
        ]);

        $builder->setEnv('HOME', $this->getContainer()->getParameter('app.root_dir'));
        $builder->setPrefix($finder->find());
        $builder->setTimeout(null);

        echo $builder->getProcess()->getCommandLine() . "\n";

        $process = $builder->getProcess();
        $process->run(function ($type, $message) {
            echo $message;
        });
    }

    /**
     * @return EntityManager
     */
    private function getEntityManager()
    {
        return $this->getContainer()->get('doctrine.orm.entity_manager');
    }
}
