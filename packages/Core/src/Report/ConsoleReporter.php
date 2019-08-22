<?php

namespace Beapp\RepositoryTester\Report;

use Beapp\RepositoryTester\Tester\MethodTester;
use Exception;
use Symfony\Component\Console\Output\OutputInterface;

class ConsoleReporter implements TestReporter
{

    /** @var OutputInterface */
    private $output;

    /** @var array */
    private $outputPerClasses = [];

    private $testSuccessCount = 0;
    private $testSkippedCount = 0;
    private $testFailedCount = 0;

    public function __construct(OutputInterface $output)
    {
        $this->output = $output;
    }

    /**
     * @inheritDoc
     */
    public function testsSessionStarted(array $methodTesters): void
    {
        $testedClasses = array_unique(array_map(function (MethodTester $methodTester) {
            return $methodTester->getTestedClass();
        }, $methodTesters));
        $methodsCount = count($methodTesters);
        $classesCount = count($testedClasses);

        $this->output->writeln("<info>Testing {$methodsCount} methods over {$classesCount} repositories</info>");
    }

    /**
     * @inheritDoc
     */
    public function testsSessionFinished(): void
    {
        $this->output->writeln('');
        $this->output->writeln('Tests session finished.');

        foreach ($this->outputPerClasses as $className => $outputPerMethods) {
            foreach ($outputPerMethods as $method => $output) {
                $this->output->writeln('');
                $this->output->writeln("On repository method $className.$method :");
                $this->output->writeln($output);
            }
        }

        $this->output->writeln('');
        $this->output->writeln("<info>Successful tests : {$this->testSuccessCount}</info>");
        $this->output->writeln("<comment>Skipped tests : {$this->testSkippedCount}</comment>");
        $this->output->writeln("<fg=red>Failed tests : {$this->testFailedCount}</>");
    }

    /**
     * @inheritDoc
     */
    public function reportSuccessTest(MethodTester $methodTester): void
    {
        $this->output->write('<info>.</info>');
        $this->testSuccessCount++;
    }

    /**
     * @inheritDoc
     */
    public function reportSkippedTest(MethodTester $methodTester, string $reason = 'Unknown reason', ?Exception $exception = null): void
    {
        $this->output->write('<comment>S</comment>');
        $this->testSkippedCount++;

        $className = $methodTester->getTestedClass();
        $methodName = $methodTester->getMethod()->getName();
        $this->outputPerClasses[$className][$methodName] = "<comment>Unable to test method \"{$className}.{$methodName}\" : {$reason}</comment>";
    }

    /**
     * @inheritDoc
     */
    public function reportErrorTest(MethodTester $methodTester, Exception $exception): void
    {
        $this->output->write('<error>E</error>');
        $this->testFailedCount++;

        $className = $methodTester->getTestedClass();
        $methodName = $methodTester->getMethod()->getName();
        $exceptionClass = get_class($exception);
        $message = $exception->getMessage();
        $this->outputPerClasses[$className][$methodName] = "<fg=red>{$exceptionClass} thrown during test of method \"{$className}.{$methodName}\" : {$message}</>";
    }

}