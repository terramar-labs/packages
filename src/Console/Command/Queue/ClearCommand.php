<?php

namespace Terramar\Packages\Console\Command\Queue;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Terramar\Packages\Console\Command\ContainerAwareCommand;

class ClearCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('resque:queue:clear')
            ->setDescription('Clear a resque queue')
            ->addArgument('queue', InputArgument::REQUIRED, 'Queue name');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var \Terramar\Packages\Helper\ResqueHelper $helper */
        $helper = $this->container->get('packages.helper.resque');
        $queue = $input->getArgument('queue');
        $jobs = $helper->clearQueue($queue);
        if ($jobs <= 0) {
            $output->writeln(sprintf('Queue "%s" is empty', $queue));
            
        } else {
            $output->writeln(sprintf('Removed %d %s from queue "%s"', $jobs, count($jobs) !== 1 ? 'jobs' : 'job', $queue));
        }

        $output->writeln('');
    }
}
