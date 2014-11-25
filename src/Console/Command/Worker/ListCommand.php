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
use Symfony\Component\Console\Output\OutputInterface;
use Terramar\Packages\Console\Command\ContainerAwareCommand;

class ListCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('resque:worker:list')
            ->setDescription('List running resque workers')
            ->addArgument('queue', InputArgument::OPTIONAL, 'Queue name', '*');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $workers = \Resque_Worker::all();

        if (count($workers) <= 0) {
            $output->writeln(array(
                'No workers running',
                ''
            ));

            return 0;
        }

        $queueFilter = $input->getArgument('queue');
        if ('*' === $queueFilter) {
            $queueFilter = false;
        }

        $workerOutput = array();
        $longestName = 0;
        foreach ($workers as $worker) {
            $queues = ($worker->queues(true) ?: array('*'));
            if ($queueFilter) {
                if (!in_array('*', $queues) && !in_array($queueFilter, $queues)) {
                    continue;
                }
            }

            $name = (string) $worker;
            $job = ($job = $worker->job())
                ? 'Processing ' . json_encode($job)
                : 'Waiting for job';

            if (strlen($job) > 20) {
                $job = substr($job, 0, 20) . '...';
            }

            $workerOutput[] = array($name, $job);

            if (($thisLength = strlen($name)) > $longestName) {
                $longestName = $thisLength;
            }
        }

        $output->writeln(sprintf("%-" . $longestName . "s\t%s", 'Worker ID', 'Current Job'));
        $loopFormat = "%-" . $longestName . "s\t<info>%s</info>";
        foreach ($workerOutput as $worker) {
            $output->writeln(sprintf($loopFormat, $worker[0], $worker[1]));
        }

        $output->writeln('');
    }
}
