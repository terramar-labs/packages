<?php

/*
 * Copyright (c) Terramar Labs
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Terramar\Packages\Console;

use Doctrine\ORM\Tools\Console\ConsoleRunner;
use Symfony\Component\Console\Application as BaseApplication;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\DialogHelper;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Terramar\Packages\Application as AppKernel;
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

    /**
     * @var \Terramar\Packages\Application
     */
    protected $app;

    public function __construct(AppKernel $app)
    {
        parent::__construct('Terramar Labs Packages', Version::VERSION);
        
        ErrorHandler::register();
        $this->app = $app;
    }

    /**
     * {@inheritDoc}
     */
    public function doRun(InputInterface $input, OutputInterface $output)
    {
        $this->setHelperSet(ConsoleRunner::createHelperSet($this->app->get('doctrine.orm.entity_manager')));
        $this->getHelperSet()->set(new QuestionHelper());
        $this->getHelperSet()->set(new DialogHelper());
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
        $this->addCommands(array(
            // Resque Commands
            new \Terramar\Packages\Console\Command\Worker\StartCommand(),
            new \Terramar\Packages\Console\Command\Worker\ListCommand(),
            new \Terramar\Packages\Console\Command\Worker\StopCommand(),
            new \Terramar\Packages\Console\Command\Queue\ListCommand(),
            new \Terramar\Packages\Console\Command\Queue\ClearCommand(),

            // DBAL Commands
            new \Doctrine\DBAL\Tools\Console\Command\RunSqlCommand(),
            new \Doctrine\DBAL\Tools\Console\Command\ImportCommand(),

            // ORM Commands
            new \Doctrine\ORM\Tools\Console\Command\ClearCache\MetadataCommand(),
            new \Doctrine\ORM\Tools\Console\Command\ClearCache\ResultCommand(),
            new \Doctrine\ORM\Tools\Console\Command\ClearCache\QueryCommand(),
            new \Doctrine\ORM\Tools\Console\Command\SchemaTool\CreateCommand(),
            new \Doctrine\ORM\Tools\Console\Command\SchemaTool\UpdateCommand(),
            new \Doctrine\ORM\Tools\Console\Command\SchemaTool\DropCommand(),
            new \Doctrine\ORM\Tools\Console\Command\EnsureProductionSettingsCommand(),
            new \Doctrine\ORM\Tools\Console\Command\ConvertDoctrine1SchemaCommand(),
            new \Doctrine\ORM\Tools\Console\Command\GenerateRepositoriesCommand(),
            new \Doctrine\ORM\Tools\Console\Command\GenerateEntitiesCommand(),
            new \Doctrine\ORM\Tools\Console\Command\GenerateProxiesCommand(),
            new \Doctrine\ORM\Tools\Console\Command\ConvertMappingCommand(),
            new \Doctrine\ORM\Tools\Console\Command\RunDqlCommand(),
            new \Doctrine\ORM\Tools\Console\Command\ValidateSchemaCommand(),
            new \Doctrine\ORM\Tools\Console\Command\InfoCommand(),
                
            // Migrations Commands
            new \Doctrine\DBAL\Migrations\Tools\Console\Command\DiffCommand(),
            new \Doctrine\DBAL\Migrations\Tools\Console\Command\ExecuteCommand(),
            new \Doctrine\DBAL\Migrations\Tools\Console\Command\GenerateCommand(),
            new \Doctrine\DBAL\Migrations\Tools\Console\Command\LatestCommand(),
            new \Doctrine\DBAL\Migrations\Tools\Console\Command\MigrateCommand(),
            new \Doctrine\DBAL\Migrations\Tools\Console\Command\StatusCommand(),
            new \Doctrine\DBAL\Migrations\Tools\Console\Command\VersionCommand()
        ));
        $registry = $this->app->get('packages.command_registry');
        foreach ($registry->getCommands() as $commandClass) {
            $this->add(new $commandClass());
        }
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
            $command->setContainer($this->app->getContainer());
        }

        return parent::add($command);
    }

    /**
     * @return array
     */
    public function getConfiguration()
    {
        return $this->app->getContainer()->getParameter('packages.configuration');
    }
}
