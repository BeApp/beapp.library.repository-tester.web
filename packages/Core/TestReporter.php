<?php

namespace Beapp\Tester\Repository;

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

    /** @var string */
    private $currentClass;

    public function setCurrentClass(string $className)
    {
        $this->currentClass = $className;

        if(!isset($this->classesReports[$this->currentClass])){
            $this->classesTests[$this->currentClass] = sprintf('%s =>   ', $this->currentClass);
            $this->classesErrors[$this->currentClass] = [];
            $this->skippedTests[$this->currentClass] = [];
        }
    }

    public function addSuccessTest()
    {
        $this->classesTests[$this->currentClass] .= '<info>.</info>';
        $this->testsCount['success']++;
    }

    public function addSkippedTest(string $method, string $reason = 'Unknown reason')
    {
        $this->classesTests[$this->currentClass] .= '<comment>S</comment>';
        $this->skippedTests[$this->currentClass][] = sprintf('Unable to test method "%s" : %s', $method, $reason);
        $this->testsCount['skipped']++;
    }

    public function buildErrorText(string $errorMessage, string $exceptionClass, string $method)
    {
        return sprintf('%s thrown during test of method "%s" : %s', $exceptionClass, $method, $errorMessage);
    }

    public function addErrorTest(string $errorMessage, string $exceptionClass, string $method)
    {
        $this->classesTests[$this->currentClass] .= '<error>E</error>';
        $this->classesErrors[$this->currentClass][] = $this->buildErrorText($errorMessage, $exceptionClass, $method);
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
