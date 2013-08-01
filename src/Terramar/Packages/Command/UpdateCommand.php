<?php

namespace Terramar\Packages\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Command\Command;
use Composer\Json\JsonFile;
use Symfony\Component\Yaml\Exception\RuntimeException;
use Terramar\Packages\Adapter\FileAdapter;
use Terramar\Packages\Adapter\SshAdapter;

/**
 * Updates the projects satis.json
 */
class UpdateCommand extends Command
{
    private static $template = array(
        'name' => 'Terramar Labs',
        'homepage' => 'http://packages.terramarlabs.com',
        'repositories' => array(),
        'require-all' => true,
        'output-dir' => null,
    );

    protected function configure()
    {
        $this
            ->setName('update')
            ->setDescription('Updates the project\'s satis.json file')
            ->setDefinition(array(
                new InputArgument('scan-dir', InputArgument::OPTIONAL, 'Directory to look for git repositories')
            ));
    }

    /**
     * @param InputInterface  $input  The input instance
     * @param OutputInterface $output The output instance
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $config = $this->getApplication()->getConfiguration();
        $remoteConfig = $config['remote'];
        $type = isset($remoteConfig['type']) ? $remoteConfig['type'] : 'file';
        $path = $remoteConfig['path'];

        $adapter = $this->getAdapter($type, $path);

        $repositories = $adapter->getRepositories();

        foreach ($repositories as $repository) {
            if (!in_array((string) $repository, $config['exclude'] ?: array())) {
                $output->writeln(sprintf('Found repository: <comment>%s</comment>', $repository));
                $data['repositories'][] = array(
                    'type' => 'vcs',
                    'url' => $config['url_prefix'] . $repository
                );
            }
        }

        $data['output-dir'] = $config['output_dir'];

        if (count($data['repositories']) > 0) {
            $fp = fopen('satis.json', 'w+');
            if (!$fp) {
                throw new \RuntimeException('Unable to open "satis.json" for writing.');
            }

            fwrite($fp, json_encode($data));

            $output->writeln(array(
                '<info>satis.json updated successfully.</info>',
            ));
        }

        $output->writeln(array(
            sprintf('<info>Found </info>%s<info> repositories.</info>', count($data['repositories'])),
        ));
    }

    private function getAdapter($type, $path)
    {
        switch ($type) {
            case 'file':
                return new FileAdapter($path);

            case 'ssh':
                return new SshAdapter($path);

            default:
                throw new RuntimeException(sprintf('Unable to locate adapter for type "%s"', $type));
        }
    }
}
