<?php

/*
 * Copyright (c) Terramar Labs
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Terramar\Packages\Plugin\Sami;

use Doctrine\ORM\EntityManager;
use Symfony\Component\Process\PhpExecutableFinder;
use Symfony\Component\Process\ProcessBuilder;
use Terramar\Packages\Entity\Package;
use Terramar\Packages\Job\ContainerAwareJob;

class UpdateJob extends ContainerAwareJob
{
    public function run($args)
    {
        $package = $this->getEntityManager()->find('Terramar\Packages\Entity\Package', $args['id']);
        if (!$package) {
            throw new \RuntimeException('Invalid project');
        }

        $config = $this->getEntityManager()->getRepository('Terramar\Packages\Plugin\Sami\PackageConfiguration')
            ->findOneBy(['package' => $package]);

        if (!$config) {
            throw new \RuntimeException('Invalid project configuration');
        }

        $cachePath = $this->getCacheDir($package);

        if (!is_dir($cachePath)) {
            mkdir($cachePath, 0777, true);
        }

        $configFilePath = $cachePath . '/config.php';
        $this->writeConfig($configFilePath, $package, $config);
        echo "Wrote config file to: $configFilePath\n";

        $finder = new PhpExecutableFinder();
        $builder = new ProcessBuilder(['vendor/bin/sami.php', 'update', $configFilePath]);
        $builder->setEnv('HOME', $this->getContainer()->getParameter('app.root_dir'));
        $builder->setPrefix($finder->find());

        $process = $builder->getProcess();
        $process->run(function ($type, $message) {
            echo $message;
        });

        if (!$process->isSuccessful()) {
            throw new \RuntimeException("Unable to generate sami documentation\n");
        }

        if ($config->getRemoteRepoPath()) {
            echo "Updating configured remote with doc changes...\n";
            $localRepoPath = $cachePath . '/remote';
            $this->emptyAndRemoveDirectory($localRepoPath);
            $buildPath = $cachePath . '/build';

            $builder = new ProcessBuilder([
                'clone',
                $config->getRemoteRepoPath(),
                $localRepoPath,
            ]);
            $builder->setPrefix('git');
            $process = $builder->getProcess();
            $process->run(function ($type, $message) {
                echo $message;
            });

            if (!$process->isSuccessful()) {
                throw new \RuntimeException('Unable to clone remote repository "' . $config->getRemoteRepoPath() . "\"\n");
            }

            echo "Copying generated files into repository...\n";
            $builder = new ProcessBuilder([
                '-R',
                $buildPath . '/',
                '.',
            ]);
            $builder->setPrefix('cp');
            $process = $builder->getProcess();
            $process->setWorkingDirectory($localRepoPath);
            $process->run(function ($type, $message) {
                echo $message;
            });

            echo "Adding all files...\n";
            $builder = new ProcessBuilder([
                'add',
                '.',
            ]);
            $builder->setPrefix('git');
            $process = $builder->getProcess();
            $process->setWorkingDirectory($localRepoPath);
            $process->run(function ($type, $message) {
                echo $message;
            });

            echo "Committing...\n";
            $builder = new ProcessBuilder([
                'commit',
                '-m',
                'Automated commit',
            ]);
            $builder->setPrefix('git');
            $process = $builder->getProcess();
            $process->setWorkingDirectory($localRepoPath);
            $process->run(function ($type, $message) {
                echo $message;
            });

            echo "Pushing...\n";
            $builder = new ProcessBuilder([
                'push',
                'origin',
            ]);
            $builder->setPrefix('git');
            $process = $builder->getProcess();
            $process->setWorkingDirectory($localRepoPath);
            $process->run(function ($type, $message) {
                echo $message;
            });
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
        return $this->getContainer()->getParameter('app.cache_dir') . '/sami/' . $package->getFqn();
    }

    private function writeConfig($configFilePath, Package $package, PackageConfiguration $config)
    {
        $cachePath = $this->getCacheDir($package);
        $tagsCode = $config->getTags() ? '    ->addFromTags(\'' . implode('\',\'',
                explode(',', $config->getTags())) . '\)' . PHP_EOL : '';
        $refsCode = '';
        foreach ($this->getRefs($config) as $ref) {
            $refsCode .= '    ->add(\'' . $ref[0] . '\', \'' . $ref[1] . '\')' . PHP_EOL;
        }

        $templatesCode = '';
        if ($templatesDir = $config->getTemplatesDir()) {
            $templatesCode = '    \'templates_dir\' => array(\'' . $templatesDir . '\'),' . "\n";
        }

        $code = <<<END
<?php

\$iterator = Symfony\Component\Finder\Finder::create()
    ->files()
    ->name('*.php')
    ->exclude('Resources')
    ->exclude('Tests')
    ->in(\$dir = '{$config->getRepositoryPath()}/{$config->getDocsPath()}');

\$versions = Sami\Version\GitVersionCollection::create(\$dir)
$tagsCode$refsCode;

return new Sami\Sami(\$iterator, array(
    'title' => '{$config->getTitle()}',
    'build_dir' => '$cachePath/build/%version%',
    'cache_dir' => '$cachePath/cache/%version%',
    'default_opened_level' => 2,
    'theme' => '{$config->getTheme()}',
$templatesCode    'versions' => \$versions
));
END;

        file_put_contents($configFilePath, $code);
    }

    private function getRefs(PackageConfiguration $config)
    {
        return array_map(function ($value) {
            return explode(':', $value);
        }, explode(',', $config->getRefs()));
    }

    private function emptyAndRemoveDirectory($directory)
    {
        $files = array_diff(scandir($directory), ['.', '..']);
        foreach ($files as $file) {
            (is_dir("$directory/$file")) ? $this->emptyAndRemoveDirectory("$directory/$file") : unlink("$directory/$file");
        }

        return rmdir($directory);
    }
}
