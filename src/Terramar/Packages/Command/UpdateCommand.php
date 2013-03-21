<?php

namespace Terramar\Packages\Command;

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
        $scanDir = realpath($input->getArgument('scan-dir') ?: $config['scan_dir']);

        if (!is_dir($scanDir)) {
            throw new \RuntimeException(sprintf('The directory "%s" does not exist.', $scanDir));
        }

        $data = static::$template;
        $iterator = new \DirectoryIterator($scanDir);
        foreach ($iterator as $file) {
            $path = $scanDir . '/' . $file;
            if (is_dir($path)
                && file_exists($path . '/HEAD')
                && !in_array((string) $file, $config['exclude'] ?: array())
            ) {
                $output->writeln(sprintf('Found repository: <comment>%s</comment>', $file));
                $data['repositories'][] = array(
                    'type' => 'vcs',
                    'url' => (string) 'git@terramarlabs.com:' . $file
                );
            }
        }

        $data['output-dir'] = $config['output_dir'];

        $fp = fopen('satis.json', 'w+');
        fwrite($fp, json_encode($data));

        $output->writeln(array(
            '',
            '<info>satis.json updated successfully.</info>',
            sprintf('<info>Found </info>%s<info> repositories.</info>', count($data['repositories'])),
        ));
    }
}
