<?php

namespace Beapp\RepositoryTester\Report;

use Beapp\RepositoryTester\Tester\MethodTester;
use Exception;
use PHPUnit\Framework\TestCase;

class PHPUnitReporter extends TestCase implements TestReporter
{

    /**
     * @inheritDoc
     */
    public function testsSessionStarted(array $methodTesters): void
    {
        // Nothing to do here
    }

    /**
     * @inheritDoc
     */
    public function testsSessionFinished(): void
    {
        // Nothing to do here
    }

    /**
     * @inheritDoc
     */
    public function reportSuccessTest(MethodTester $methodTester): void
    {
        self::assertTrue(true);
    }

    /**
     * @inheritDoc
     */
    public function reportSkippedTest(MethodTester $methodTester, string $reason = 'Unknown reason', ?Exception $exception = null): void
    {
        $this->markTestSkipped($reason);
    }

    /**
     * @inheritDoc
     */
    public function reportErrorTest(MethodTester $methodTester, Exception $exception): void
    {
        $className = $methodTester->getTestedClass();
        $methodName = $methodTester->getMethod()->getName();
        $errorMessage = $exception->getMessage();

        $this->fail("Repository method $methodName from $className is not valid :\n$errorMessage");
    }

}
