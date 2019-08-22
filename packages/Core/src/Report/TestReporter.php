<?php

namespace Beapp\RepositoryTester\Report;

use Beapp\RepositoryTester\Tester\MethodTester;
use Exception;

interface TestReporter
{

    /**
     * @param MethodTester[] $methodTesters
     */
    public function testsSessionStarted(array $methodTesters): void;

    public function testsSessionFinished(): void;

    /**
     * @param MethodTester $methodTester
     */
    public function reportSuccessTest(MethodTester $methodTester): void;

    /**
     * @param MethodTester $methodTester
     * @param string $reason
     * @param Exception|null $exception
     */
    public function reportSkippedTest(MethodTester $methodTester, string $reason = 'Unknown reason', ?Exception $exception = null): void;

    /**
     * @param MethodTester $methodTester
     * @param Exception $exception
     */
    public function reportErrorTest(MethodTester $methodTester, Exception $exception): void;

}
