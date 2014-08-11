<?php

namespace Terramar\Packages\Console\Command\Queue;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Terramar\Packages\Console\Command\ContainerAwareCommand;

class ListCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('resque:queue:list')
            ->setDescription('List jobs in a resque queue')
            ->addArgument('queue', InputArgument::OPTIONAL, 'Queue name', '*');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var \Terramar\Packages\Helper\ResqueHelper $helper */
        $helper = $this->container->get('packages.helper.resque');
        $queue = $input->getArgument('queue');
        $jobs = $helper->getJobs($queue);
        if (count($jobs) <= 0) {
            if ($queue === '*') {
                $output->writeln(sprintf('All queues empty', $queue));
                
            } else {
                $output->writeln(sprintf('Queue "%s" is empty', $queue));
            }
            
            $output->writeln('');
            
            return;
        }
        
        $count = count($jobs);
        $output->writeln(sprintf('Queue %s contains %d %s:', $queue, $count, $count !== 1 ? 'entries' : 'entry'));
        foreach ($jobs as $job) {
            $output->writeln(sprintf('<info>%s</info>', $job));
        }

        $output->writeln('');
    }
}
