<?php

/*
 * Copyright (c) Terramar Labs
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Terramar\Packages\Console\Command\Worker;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\PhpExecutableFinder;
use Symfony\Component\Process\Process;
use Terramar\Packages\Console\Command\ContainerAwareCommand;
use Terramar\Packages\Helper\ResqueHelper;

class StartCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('resque:worker:start')
            ->setDescription('Start a resque worker')
            ->addArgument('queues', InputArgument::OPTIONAL, 'Queue names (separate using comma)', '*')
            ->addOption('count', 'c', InputOption::VALUE_REQUIRED, 'How many workers to fork', 1)
            ->addOption('interval', 'i', InputOption::VALUE_REQUIRED,
                'How often to check for new jobs across the queues', 5)
            ->addOption('foreground', 'f', InputOption::VALUE_NONE, 'Should the worker run in foreground')
            ->addOption('memory-limit', 'm', InputOption::VALUE_REQUIRED,
                'Force cli memory_limit (expressed in Mbytes)');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $env = [
            'QUEUE'    => $input->getArgument('queues'),
            'VERBOSE'  => $input->getOption('verbose'),
            'COUNT'    => $input->getOption('count'),
            'INTERVAL' => $input->getOption('interval'),
            'PREFIX'   => 'resque:',
        ];

        $resqueConfig = ResqueHelper::autoConfigure($this->container);
        $env['REDIS_BACKEND'] = $resqueConfig['backend'];
        $env['REDIS_BACKEND_DB'] = $resqueConfig['database'];

        $opt = '';
        if (0 !== $m = (int)$input->getOption('memory-limit')) {
            $opt = sprintf('-d memory_limit=%dM', $m);
        }

        $workerCommand = strtr('%bin% %opt% %dir%/bin/resque', [
            '%bin%' => $this->getPhpBinary(),
            '%opt%' => $opt,
            '%dir%' => $this->container->getParameter('app.root_dir'),
        ]);

        if (!$input->getOption('foreground')) {
            $workerCommand = strtr('nohup %cmd% > %logs_dir%/resque.log 2>&1 & echo $!', [
                '%cmd%'      => $workerCommand,
                '%logs_dir%' => $this->container->getParameter('app.log_dir'),
            ]);
        }

        // In windows: When you pass an environment to CMD it replaces the old environment
        // That means we create a lot of problems with respect to user accounts and missing vars
        // this is a workaround where we add the vars to the existing environment.
        if (defined('PHP_WINDOWS_VERSION_BUILD')) {
            foreach ($env as $key => $value) {
                putenv($key . '=' . $value);
            }
            $env = null;
        }

        $process = new Process($workerCommand, null, $env, null, null);

        if (!$input->getOption('quiet')) {
            $output->writeln(sprintf('Executing <info>%s</info>...', $process->getCommandLine()));
        }

        if ($input->getOption('foreground')) {
            $process->run(function ($type, $buffer) use ($output) {
                $output->write($buffer);
            });
        } else {
            $process->run();
            if (function_exists('gethostname')) {
                $hostname = gethostname();
            } else {
                $hostname = php_uname('n');
            }

            if (!$input->getOption('quiet')) {
                $workers = $env['COUNT'];
                $output->writeln(sprintf(
                    'Starting <info>%s %s</info> on <info>%s</info> for <info>%s</info> queues',
                    $workers,
                    $workers != 1 ? 'workers' : 'worker',
                    $hostname,
                    $input->getArgument('queues')
                ));
            }
        }

        $output->writeln('');
    }

    private function getPhpBinary()
    {
        $finder = new PhpExecutableFinder();

        return $finder->find();
    }
}
