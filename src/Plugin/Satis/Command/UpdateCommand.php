<?php

/*
 * Copyright (c) Terramar Labs
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Terramar\Packages\Plugin\Satis\Command;

use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Terramar\Packages\Console\Command\ContainerAwareCommand;
use Terramar\Packages\Entity\Package;

/**
 * Updates the projects satis.json.
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
                new InputOption('build', 'b', InputOption::VALUE_NONE, 'Build packages.json after update'),
                new InputOption('skip-errors', null, InputOption::VALUE_NONE, 'Skip Download or Archive errors'),
            ));
    }

    /**
     * @param InputInterface  $input  The input instance
     * @param OutputInterface $output The output instance
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $configHelper = $this->container->get('packages.plugin.satis.config_helper');
        $configFile = $configHelper->generateConfiguration();
        $skipErrors = (bool) $input->getOption('skip-errors');

        $data = json_decode(file_get_contents($configFile), true);

        foreach ($data['repositories'] as $repository) {
            $output->writeln(sprintf('Found repository: <comment>%s</comment>', $repository['url']));
        }

        $output->writeln(array(
            '<info>satis.json updated successfully.</info>',
        ));

        $output->writeln(array(
            sprintf('<info>Found </info>%s<info> repositories.</info>', count($data['repositories'])),
        ));

        if ($input->getOption('build')) {
            $command = $this->getApplication()->find('satis:build');
            $input = new ArrayInput(['file' => $configFile, '--skip-errors' => $skipErrors]);
            $command->run($input, $output);
        }
    }
}
