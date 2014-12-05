<?php

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
