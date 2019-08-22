<?php

namespace Beapp\RepositoryTesterBundle\Command;

use Beapp\RepositoryTester\Report\ConsoleReporter;
use Beapp\RepositoryTester\RepositoryTester;
use ReflectionException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ValidateRepositoryCommand extends Command
{
    /** @var RepositoryTester */
    private $repositoryTester;

    /**
     * TestRepositoryCommand constructor.
     *
     * @param RepositoryTester $repositoryTester
     */
    public function __construct(RepositoryTester $repositoryTester)
    {
        parent::__construct('beapp:repository:validate');

        $this->repositoryTester = $repositoryTester;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|void|null
     * @throws ReflectionException
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $consoleReporter = new ConsoleReporter($output);

        $methodTesters = $this->repositoryTester->crawlMethodTesters();
        $this->repositoryTester->executeTests($consoleReporter, $methodTesters);
    }
}
