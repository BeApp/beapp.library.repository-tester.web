<?php

namespace Beapp\RepositoryTester\Report;

use Symfony\Component\Console\Output\OutputInterface;

abstract class TestReporter
{
    /** @var array */
    protected $classesTests = [];

    /** @var array */
    protected $classesErrors = [];

    /** @var array */
    protected $skippedTests = [];

    /** @var int[] */
    protected $testsCount = [
        'success' => 0,
        'skipped' => 0,
        'failed' => 0,
    ];

    public abstract function setCurrentClass(string $className);

    public abstract function addSuccessTest(string $className);

    public abstract function addSkippedTest(string $className, string $method, string $reason = 'Unknown reason');

    public abstract function buildErrorText(string $errorMessage, string $exceptionClass, string $method);

    public abstract function addErrorToReport(string $className, string $methodName, string $errorMessage, string $exceptionClass);

    public abstract function buildReporting(OutputInterface $output);

}
