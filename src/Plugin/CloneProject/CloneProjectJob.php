<?php

/*
 * Copyright (c) Terramar Labs
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Terramar\Packages\Plugin\CloneProject;

use Doctrine\ORM\EntityManager;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Process\ProcessBuilder;
use Terramar\Packages\Entity\Package;
use Terramar\Packages\Job\ContainerAwareJob;

class CloneProjectJob extends ContainerAwareJob
{
    public function run($args)
    {
        $package = $this->getEntityManager()->find('Terramar\Packages\Entity\Package', $args['id']);
        if (!$package) {
            throw new \RuntimeException('Invalid project');
        }

        $directory = $this->getCacheDir($package);

        if (file_exists($directory) || is_dir($directory)) {
            $this->emptyAndRemoveDirectory($directory);
        }

        mkdir($directory, 0777, true);

        $builder = new ProcessBuilder(['clone', $package->getSshUrl(), $directory]);
        $builder->setPrefix('git');

        $process = $builder->getProcess();
        $process->run(function ($type, $message) {
            echo $message;
        });

        if ($process->isSuccessful()) {
            $event = new PackageCloneEvent($package, $directory);
            $this->getEventDispatcher()->dispatch(Events::PACKAGE_CLONED, $event);
        }
    }

    /**
     * @return EntityManager
     */
    private function getEntityManager()
    {
        return $this->getContainer()->get('doctrine.orm.entity_manager');
    }

    /**
     * @return string
     */
    private function getCacheDir(Package $package)
    {
        return $this->getContainer()->getParameter('app.cache_dir') . '/cloned_project/' . $package->getFqn();
    }

    private function emptyAndRemoveDirectory($directory)
    {
        $files = array_diff(scandir($directory), ['.', '..']);
        foreach ($files as $file) {
            (is_dir("$directory/$file")) ? $this->emptyAndRemoveDirectory("$directory/$file") : unlink("$directory/$file");
        }

        return rmdir($directory);
    }

    /**
     * @return EventDispatcherInterface
     */
    private function getEventDispatcher()
    {
        return $this->getContainer()->get('event_dispatcher');
    }
}
