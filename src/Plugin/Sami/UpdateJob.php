<?php

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
        
        $configFilePath = $this->getCacheDir($package) . '/config.php';
        $this->writeConfig($configFilePath, $package, $config);
        
        $finder = new PhpExecutableFinder();
        $builder = new ProcessBuilder(array('vendor/bin/sami', 'update', $configFilePath));
        $builder->setEnv('HOME', $this->getContainer()->getParameter('app.root_dir'));
        $builder->setPrefix($finder->find());

        $process = $builder->getProcess();
        $process->run(function($type, $message) {
                echo $message;
            });
    }

    private function writeConfig($configFilePath, Package $package, PackageConfiguration $config) 
    {
        $cachePath = $this->getCacheDir($package);
        
        $code = <<<END
<?php

\$versions = Sami\Version\GitVersionCollection::create('{$config->getRepositoryPath()}')
    ->addFromTags()
    ->add('master', 'master branch')
    ->add('develop', 'develop branch')
;

return new Sami\Sami('{$config->getRepositoryPath()}', array(
    'title' => '{$package->getName()}',
    'build_dir' => '$cachePath/build/%version%',
    'cache_dir' => '$cachePath/cache/%version%',
    'default_opened_level' => 2,
    'theme' => 'enhanced',
    'versions' => \$versions
));
END;
        
        file_put_contents($configFilePath, $code);
    }
}