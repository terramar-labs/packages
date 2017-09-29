<?php

/*
 * Copyright (c) Terramar Labs
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Terramar\Packages\Plugin\Satis\Command;

use Composer\Satis\Console\Command\BuildCommand as BaseCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Wraps Satis build command.
 */
class BuildCommand extends BaseCommand implements ContainerAwareInterface
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    protected function configure()
    {
        parent::configure();

        $this->setName('satis:build');
        $def = $this->getDefinition();
        $args = $def->getArguments();
        $args['file'] = new InputArgument('file', InputArgument::OPTIONAL, 'Satis configuration file. If left blank, one will be generated.');
        $def->setArguments($args);
    }

    /**
     * @param InputInterface $input The input instance
     * @param OutputInterface $output The output instance
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if (!$input->getArgument('file')) {
            $configHelper = $this->container->get('packages.plugin.satis.config_helper');
            $configFile = $configHelper->generateConfiguration();
            $input->setArgument('file', $configFile);
        }

        parent::execute($input, $output);
    }

    /**
     * Sets the container.
     *
     * @param ContainerInterface|null $container A ContainerInterface instance or null
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }
}
