<?php

namespace Terramar\Packages\Command\Worker;

use Terramar\Packages\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class StopCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('resque:worker:stop')
            ->setDescription('Stop a resque worker')
            ->addArgument('id', InputArgument::OPTIONAL, 'Worker id')
            ->addOption('all', 'a', InputOption::VALUE_NONE, 'Should kill all workers')
            ->addOption('force', 'f', InputOption::VALUE_NONE, 'Force kill all workers, immediately');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if ($input->getOption('all')) {
            $workers = \Resque_Worker::all();

        } else {
            $worker = \Resque_Worker::find($input->getArgument('id'));

            if (!$worker) {
                $availableWorkers = \Resque_Worker::all();
                if (!empty($availableWorkers)) {
                    throw new \RuntimeException('A running worker must be specified');
                }
            }

            $workers = $worker ? array($worker) : array();
        }

        if (count($workers) <= 0) {
            $output->writeln(array(
                    'No workers running',
                    ''
                ));

            return;
        }

        $signal = $input->getOption('force') ? SIGTERM : SIGQUIT;
        foreach ($workers as $worker) {
            $output->writeln(sprintf('%s %s...', $signal === SIGTERM ? 'Force stopping' : 'Stopping', $worker));
            list ( , $pid) = explode(':', (string) $worker);

            posix_kill($pid, $signal);
        }

        $output->writeln('');
    }
}
