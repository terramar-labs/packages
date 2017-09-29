<?php

/*
 * Copyright (c) Terramar Labs
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Terramar\Packages\Console;

class CommandRegistry
{
    private $commands = array();

    public function addCommand($className)
    {
        $this->commands[] = $className;
    }

    public function getCommands()
    {
        return $this->commands;
    }
}
