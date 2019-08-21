<?php

namespace Beapp\RepositoryTester\Report;

use Symfony\Component\Console\Output\OutputInterface;

class PHPUnitReporter extends TestReporter
{

    public function setCurrentClass(string $className)
    {
        // TODO: Implement setCurrentClass() method.
    }

    public function addSuccessTest(string $className)
    {
        // TODO: Implement addSuccessTest() method.
    }

    public function addSkippedTest(string $className, string $method, string $reason = 'Unknown reason')
    {
        // TODO: Implement addSkippedTest() method.
    }

    public function buildErrorText(string $errorMessage, string $exceptionClass, string $method)
    {
        return $errorMessage;
    }

    public function addErrorToReport(string $className, string $methodName, string $errorMessage, string $exceptionClass)
    {
        // TODO: Implement addErrorToReport() method.
    }

    public function buildReporting(OutputInterface $output)
    {
        // TODO: Implement buildReporting() method.
    }
}