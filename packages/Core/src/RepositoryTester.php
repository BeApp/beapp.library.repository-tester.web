<?php

namespace Beapp\RepositoryTester;

use Beapp\RepositoryTester\Crawler\RepositoryCrawler;
use Beapp\RepositoryTester\Exception\MethodTestException;
use Beapp\RepositoryTester\Report\TestReporter;
use Beapp\RepositoryTester\Tester\MethodTester;
use Beapp\RepositoryTester\Tester\MethodTesterFactory;
use Exception;
use Psr\Log\LoggerInterface;
use ReflectionException;

class RepositoryTester
{
    /** @var LoggerInterface */
    private $logger;
    /** @var RepositoryCrawler */
    private $repositoryCrawler;
    /** @var MethodTesterFactory */
    private $methodTesterFactory;

    /**
     * RepositoryTester constructor.
     * @param LoggerInterface $logger
     * @param RepositoryCrawler $repositoryCrawler
     * @param MethodTesterFactory $methodTesterFactory
     */
    public function __construct(LoggerInterface $logger, RepositoryCrawler $repositoryCrawler, MethodTesterFactory $methodTesterFactory)
    {
        $this->logger = $logger;
        $this->repositoryCrawler = $repositoryCrawler;
        $this->methodTesterFactory = $methodTesterFactory;
    }

    /**
     * @return MethodTester[]
     * @throws ReflectionException
     */
    public function crawlMethodTesters(): array
    {
        $this->logger->info('Start crawling repos');

        $repositories = $this->repositoryCrawler->lookupRepositoriesFromMetadata();
        $methodsTesters = [];

        foreach ($repositories as $repository) {
            $reflectionMethods = $this->repositoryCrawler->lookupMethodsFromRepository($repository);

            foreach ($reflectionMethods as $reflectionMethod) {
                $methodsTesters[] = $this->methodTesterFactory->buildMethodTester($reflectionMethod, $repository);
            }
        }

        return $methodsTesters;
    }

    /**
     * @param TestReporter $testReporter
     * @param MethodTester[] $methodTesters
     */
    public function executeTests(TestReporter $testReporter, array $methodTesters): void
    {
        $testReporter->testsSessionStarted($methodTesters);

        foreach ($methodTesters as $methodTester) {
            $this->testMethod($testReporter, $methodTester);
        }

        $testReporter->testsSessionFinished();
    }

    /**
     * @param TestReporter $testReporter
     * @param MethodTester $methodTester
     */
    public function testMethod(TestReporter $testReporter, MethodTester $methodTester): void
    {
        try {
            $methodTester->test();

            $testReporter->reportSuccessTest($methodTester);
        } catch (MethodTestException $e) {
            $testReporter->reportSkippedTest($methodTester, $e->getMessage(), $e);
        } catch (Exception $e) {
            $testReporter->reportErrorTest($methodTester, $e);
        }
    }
}
