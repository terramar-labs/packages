<?php

namespace Terramar\Packages\Console;

use Symfony\Component\Console\Application as BaseApplication;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Terramar\Packages\Application as AppKernel;
use Composer\Satis\Command\BuildCommand;
use Symfony\Component\Yaml\Yaml;
use Terramar\Packages\Command\UpdateCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
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

    /**
     * @var \Terramar\Packages\Application
     */
    protected $app;

    public function __construct(AppKernel $app)
    {
        parent::__construct('Terramar Labs Packages', Version::VERSION);
        ErrorHandler::register();
        $this->config = Yaml::parse('config.yml');
        $this->config = $this->config['satis'];
        $this->app = $app;
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
     * Adds a command object.
     *
     * If a command with the same name already exists, it will be overridden.
     *
     * @param Command $command A Command object
     *
     * @return Command The registered command
     *
     * @api
     */
    public function add(Command $command)
    {
        if ($command instanceof ContainerAwareInterface) {
            $command->setContainer($this->app);
        }

        return parent::add($command);
    }

    /**
     * @return array
     */
    public function getConfiguration()
    {
        return $this->config;
    }
}
