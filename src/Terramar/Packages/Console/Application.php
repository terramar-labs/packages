<?php

namespace Terramar\Packages\Console;

use Symfony\Component\Console\Application as BaseApplication;
use Composer\Satis\Command\BuildCommand;
use Symfony\Component\Yaml\Yaml;
use Terramar\Packages\Command\UpdateCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Composer\Satis\Command;
use Composer\IO\ConsoleIO;
use Composer\Factory;
use Composer\Util\ErrorHandler;
use Terramar\Packages\Version;

/**
 * @author Jordi Boggiano <j.boggiano@seld.be>
 */
class Application extends BaseApplication
{
    protected $io;
    protected $composer;
    protected $config;

    public function __construct()
    {
        parent::__construct('Terramar Labs Packages', Version::VERSION);
        ErrorHandler::register();
        $this->config = Yaml::parse('config.yml');
        $this->config = $this->config['satis'];
    }

    /**
     * {@inheritDoc}
     */
    public function doRun(InputInterface $input, OutputInterface $output)
    {
        $this->registerCommands();
        $this->io = new ConsoleIO($input, $output, $this->getHelperSet());

        return parent::doRun($input, $output);
    }

    /**
     * @return Composer
     */
    public function getComposer($required = true, $config = null)
    {
        if (null === $this->composer) {
            try {
                $this->composer = Factory::create($this->io, $config);
            } catch (\InvalidArgumentException $e) {
                $this->io->write($e->getMessage());
                exit(1);
            }
        }

        return $this->composer;
    }

    /**
     * Initializes all the composer commands
     */
    protected function registerCommands()
    {
        $this->add(new BuildCommand());
        $this->add(new UpdateCommand());
    }

    /**
     * @return array
     */
    public function getConfiguration()
    {
        return $this->config;
    }
}
