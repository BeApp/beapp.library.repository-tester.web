<?php

namespace Beapp\RepositoryTesterBundle\Command;

use Beapp\RepositoryTesterBundle\RepositoryTester;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class TestRepositoryCommand extends Command
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
        parent::__construct('beapp:repository:test');

        $this->repositoryTester = $repositoryTester;
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('Begin to test repositories...');

        $testReport = $this->repositoryTester->crawlRepositories();

        $output->writeln('<comment>End of the tests ! Here is the reporting :</comment>');

        $testReport->buildReporting($output);
    }
}
