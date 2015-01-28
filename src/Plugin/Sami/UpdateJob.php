<?php

/*
 * Copyright (c) Terramar Labs
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Terramar\Packages\Plugin\Sami;

use Doctrine\ORM\EntityManager;
use Symfony\Component\Process\ExecutableFinder;
use Symfony\Component\Process\PhpExecutableFinder;
use Symfony\Component\Process\ProcessBuilder;
use Terramar\Packages\Entity\Package;
use Terramar\Packages\Job\ContainerAwareJob;

class UpdateJob extends ContainerAwareJob
{
    /**
     * @return string
     */
    private function getCacheDir(Package $package)
    {
        return $this->getContainer()->getParameter('app.cache_dir') . '/sami/' . $package->getFqn();
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
        $package = $this->getEntityManager()->find('Terramar\Packages\Entity\Package', $args['id']);
        if (!$package) {
            throw new \RuntimeException('Invalid project');
        }
        
        $config = $this->getEntityManager()->getRepository('Terramar\Packages\Plugin\Sami\PackageConfiguration')
            ->findOneBy(array('package' => $package));
        
        if (!$config) {
            throw new \RuntimeException('Invalid project configuration');
        }
        
        $cachePath = $this->getCacheDir($package);

        if (!is_dir($cachePath)) {
            mkdir($cachePath, 0777, true);
        }

        $configFilePath = $cachePath . '/config.php';
        $this->writeConfig($configFilePath, $package, $config);

        $finder = new PhpExecutableFinder();
        $builder = new ProcessBuilder(array('vendor/bin/sami.php', 'update', $configFilePath));
        $builder->setEnv('HOME', $this->getContainer()->getParameter('app.root_dir'));
        $builder->setPrefix($finder->find());

        $process = $builder->getProcess();
        $process->run(function($type, $message) {
                echo $message;
            });

        if ($config->getRemoteRepoPath()) {
            $localRepoPath = $cachePath . '/remote';
            $this->emptyAndRemoveDirectory($localRepoPath);
            $buildPath = $cachePath . '/build';

            $builder = new ProcessBuilder(array(
                'clone',
                $config->getRemoteRepoPath(),
                $localRepoPath
            ));
            $builder->setPrefix('git');
            $process = $builder->getProcess();
            $process->run(function ($type, $message) {
                echo $message;
            });

            foreach ($this->getRefs($config) as $ref) {
                $builder = new ProcessBuilder(array(
                    'checkout',
                    $ref[0]
                ));
                $builder->setPrefix('git');
                $process = $builder->getProcess();
                $process->setWorkingDirectory($localRepoPath);
                $process->run(function ($type, $message) {
                    echo $message;
                });

                $builder = new ProcessBuilder(array(
                    '-R',
                    $buildPath.'/'.$ref[0].'/',
                    '.'
                ));
                $builder->setPrefix('cp');
                $process = $builder->getProcess();
                $process->setWorkingDirectory($localRepoPath);
                $process->run(function ($type, $message) {
                    echo $message;
                });

                $builder = new ProcessBuilder(array(
                    'add',
                    '.'
                ));
                $builder->setPrefix('git');
                $process = $builder->getProcess();
                $process->setWorkingDirectory($localRepoPath);
                $process->run(function ($type, $message) {
                    echo $message;
                });

                $builder = new ProcessBuilder(array(
                    'commit',
                    '-m',
                    'Automated commit'
                ));
                $builder->setPrefix('git');
                $process = $builder->getProcess();
                $process->setWorkingDirectory($localRepoPath);
                $process->run(function ($type, $message) {
                    echo $message;
                });

                $builder = new ProcessBuilder(array(
                    'push',
                    'origin',
                    $ref[0]
                ));
                $builder->setPrefix('git');
                $process = $builder->getProcess();
                $process->setWorkingDirectory($localRepoPath);
                $process->run(function ($type, $message) {
                    echo $message;
                });
            }
        }
    }

    private function getRefs(PackageConfiguration $config)
    {
        return array_map(function($value) {
            return explode(':', $value);
        }, explode(',', $config->getRefs()));
    }

    private function writeConfig($configFilePath, Package $package, PackageConfiguration $config) 
    {
        $cachePath = $this->getCacheDir($package);
        $tagsCode = $config->getTags() ? '    ->addFromTags(\'' . implode('\',\'', explode(',', $config->getTags())) . '\)'.PHP_EOL : '';
        $refsCode = '';
        foreach ($this->getRefs($config) as $ref) {
            $refsCode .= '    ->add(\'' . $ref[0] . '\', \'' . $ref[1] . '\')'.PHP_EOL;
        }

        $code = <<<END
<?php

/*
 * Copyright (c) Terramar Labs
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

\$versions = Sami\Version\GitVersionCollection::create('{$config->getRepositoryPath()}/{$config->getDocsPath()}')
$tagsCode$refsCode;

return new Sami\Sami('{$config->getRepositoryPath()}/{$config->getDocsPath()}', array(
    'title' => '{$config->getTitle()}',
    'build_dir' => '$cachePath/build/%version%',
    'cache_dir' => '$cachePath/cache/%version%',
    'default_opened_level' => 2,
    'theme' => '{$config->getTheme()}',
    'versions' => \$versions
));
END;
        
        file_put_contents($configFilePath, $code);
    }

    private function emptyAndRemoveDirectory($directory)
    {
        $files = array_diff(scandir($directory), array('.','..'));
        foreach ($files as $file) {
            (is_dir("$directory/$file")) ? $this->emptyAndRemoveDirectory("$directory/$file") : unlink("$directory/$file");
        }

        return rmdir($directory);
    }
}
