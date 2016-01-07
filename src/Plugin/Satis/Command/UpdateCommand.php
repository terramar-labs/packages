<?php

namespace Terramar\Packages\Plugin\Satis\Command;

use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Terramar\Packages\Console\Command\ContainerAwareCommand;
use Terramar\Packages\Entity\Package;

/**
 * Updates the projects satis.json
 */
class UpdateCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('satis:update')
            ->setDescription('Updates the project\'s satis.json file')
            ->setDefinition(array(
                new InputArgument('scan-dir', InputArgument::OPTIONAL, 'Directory to look for git repositories'),
                new InputOption('build', 'b', InputOption::VALUE_NONE, 'Build packages.json after update')
            ));
    }

    /**
     * @param InputInterface  $input  The input instance
     * @param OutputInterface $output The output instance
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $config = $this->getApplication()->getConfiguration();
        $data = array(
            'name'                     => 'Simple static Composer repository generator',
            'homepage'                 => 'https://github.com/composer/satis',
            'output-dir'               => realpath($config['output_dir']),
            'repositories'             => array(),
            'output-html'              => false,
            'require-dependencies'     => true,
            'require-dev-dependencies' => true,
        );

        $packages = $this->container->get('doctrine.orm.entity_manager')->getRepository('Terramar\Packages\Entity\Package')->findBy(array('enabled' => true));

        $repositories = array_map(function(Package $package) {
            return $package->getSshUrl();
        }, $packages);

        foreach ($repositories as $repository) {
            $output->writeln(sprintf('Found repository: <comment>%s</comment>', $repository));
            $data['repositories'][] = array(
                'type' => 'vcs',
                'url' => $repository
            );
        }

        $fp = fopen('satis.json', 'w+');
        if (!$fp) {
            throw new \RuntimeException('Unable to open "satis.json" for writing.');
        }

        fwrite($fp, json_encode($data, JSON_PRETTY_PRINT));

        $output->writeln(array(
            '<info>satis.json updated successfully.</info>',
        ));

        $output->writeln(array(
            sprintf('<info>Found </info>%s<info> repositories.</info>', count($data['repositories'])),
        ));

        if ($input->getOption('build')) {
            $command = $this->getApplication()->find('satis:build');

            $input = new ArrayInput(array(''));
            $command->run($input, $output);
        }
    }
}
