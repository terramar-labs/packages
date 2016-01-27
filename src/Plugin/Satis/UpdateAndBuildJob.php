<?php

/*
 * Copyright (c) Terramar Labs
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Terramar\Packages\Plugin\Satis;

use Symfony\Component\Process\PhpExecutableFinder;
use Symfony\Component\Process\ProcessBuilder;
use Terramar\Packages\Job\ContainerAwareJob;

class UpdateAndBuildJob extends ContainerAwareJob
{
    public function run($args)
    {
        $finder = new PhpExecutableFinder();
        $builder = new ProcessBuilder(array('bin/console', 'satis:update', '--build'));
        $builder->setEnv('HOME', $this->getContainer()->getParameter('app.root_dir'));
        $builder->setPrefix($finder->find());

        $process = $builder->getProcess();
        $process->run(function ($type, $message) {
            echo $message;
        });
    }
}
