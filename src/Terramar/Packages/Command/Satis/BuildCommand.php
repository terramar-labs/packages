<?php

namespace Terramar\Packages\Command\Satis;

use Composer\Satis\Command\BuildCommand as BaseCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Command\Command;

/**
 * Wraps Satis build command
 */
class BuildCommand extends BaseCommand
{
    protected function configure()
    {
        parent::configure();
        
        $this->setName('satis:build');
    }
}
