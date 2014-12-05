<?php

namespace Terramar\Packages\Plugin\Satis\Command;

use Composer\Satis\Command\BuildCommand as BaseCommand;

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
