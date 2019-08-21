<?php

namespace Beapp\RepositoryTester;

use Beapp\RepositoryTester\Crawler\RepositoryCrawler;
use Beapp\RepositoryTester\Report\TestReporter;
use Beapp\RepositoryTester\Tester\MethodTester;
use Beapp\RepositoryTester\Tester\MethodTesterFactory;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
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
        foreach ($methodTesters as $methodTester) {
            $this->testMethod($testReporter, $methodTester);
        }
    }

    /**
     * @param TestReporter $testReporter
     * @param MethodTester $methodTester
     * @return array
     */
    public function testMethod(TestReporter $testReporter, MethodTester $methodTester)
    {
        $methodName = $methodTester->getMethod()->getName();
        $className = $methodTester->getTestedClass();

        $result = [
            'success' => true,
            'reason' => '',
        ];

        try {
            $methodTester->test();

            $testReporter->addSuccessTest($className);
        } catch (NoResultException|NonUniqueResultException $e) {
            $this->logger->info('Exception thrown during test of method ' . $methodName, ['errorMsg' => $e->getMessage()]);

            $testReporter->addSkippedTest($className, $methodTester->getMethod()->getName(), $e->getMessage());
        } catch (Exception $e) {
            $result['reason'] = $testReporter->buildErrorText(
                $e->getMessage(),
                get_class($e),
                $methodName
            );

            $result['success'] = false;

            $testReporter->addErrorToReport($className, $methodName, $e->getMessage(), get_class($e));
        }

        return $result;
    }
}
