<?php

namespace Beapp\RepositoryTesterBundle\Service;

use Symfony\Component\Console\Output\OutputInterface;

class TestReporter
{
    /** @var array */
    private $classesTests = [];

    /** @var array */
    private $classesErrors = [];

    private $skippedTests = [];

    /** @var int[] */
    private $testsCount =  [
        'success' => 0,
        'skipped' => 0,
        'failed'  => 0,
    ];

    public function setCurrentClass(string $className)
    {
        if(!isset($this->classesReports[$className])){
            $this->classesTests[$className] = sprintf('%s =>   ', $className);
            $this->classesErrors[$className] = [];
            $this->skippedTests[$className] = [];
        }
    }

    public function addSuccessTest(string $className)
    {
        $this->setCurrentClass($className);
        $this->classesTests[$className] .= '<info>.</info>';
        $this->testsCount['success']++;
    }

    public function addSkippedTest(string $className, string $method, string $reason = 'Unknown reason')
    {
        $this->setCurrentClass($className);
        $this->classesTests[$className] .= '<comment>S</comment>';
        $this->skippedTests[$className][] = sprintf('Unable to test method "%s" : %s', $method, $reason);
        $this->testsCount['skipped']++;
    }

    public function buildErrorText(string $errorMessage, string $exceptionClass, string $method)
    {
        return sprintf('%s thrown during test of method "%s" : %s', $exceptionClass, $method, $errorMessage);
    }

    public function addErrorToReport(string $className, string $methodName, string $errorMessage, string $exceptionClass)
    {
        $this->setCurrentClass($className);
        $this->classesTests[$className] .= '<error>E</error>';
        $this->classesErrors[$className][] = $this->buildErrorText($errorMessage, $exceptionClass, $methodName);
        $this->testsCount['failed']++;
    }

    public function buildReporting(OutputInterface $output)
    {
        $output->writeln('<info>Classes tested : '.count($this->classesTests));
        $output->writeln('Successful tests : '.$this->testsCount['success']);
        $output->writeln('Skipped tests : '.$this->testsCount['skipped']);
        $output->writeln('Failed tests : '.$this->testsCount['failed'].'</info>');

        foreach($this->classesTests as $className => $classTest)
        {
            $output->writeln('');
            $output->writeln($classTest);

            foreach($this->skippedTests[$className] as $skippedTest)
            {
                $output->writeln('<comment>'.$skippedTest.'</comment>');
            }

            foreach($this->classesErrors[$className] as $classError)
            {
                $output->writeln('<error>'.$classError.'</error>');
            }
        }
    }
}
