<?php

namespace Terramar\Satis\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Command\Command;
use Composer\Json\JsonFile;

/**
 * Updates the projects satis.json
 */
class UpdateCommand extends Command
{
    private static $template = array(
        'name' => 'Terramar Labs',
        'homepage' => 'http://packages.terramarlabs.com',
        'repositories' => array(),
        'require-all' => true
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
        $scanDir = realpath($input->getArgument('scan-dir'));

        if (!is_dir($scanDir)) {
            throw new \RuntimeException(sprintf('The directory "%s" does not exist.', $scanDir));
        }

        $data = static::$template;
        $iterator = new \DirectoryIterator($scanDir);
        foreach ($iterator as $file) {
            $path = $scanDir . '/' . $file;
            if (is_dir($path) && file_exists($path . '/HEAD')) {
                $data['repositories'][] = array(
                    'type' => 'vcs',
                    'url' => (string) 'git@terramarlabs.com:' . $file
                );
            }
        }

        $fp = fopen('satis.json', 'w+');
        fwrite($fp, json_encode($data));

        $output->writeln(array(
            sprintf('satis.json updated successfully. Found %s repositories.', count($data['repositories'])),
            ''
        ));
    }
}
