<?php

namespace Beapp\RepositoryTesterBundle\Service\Repository;

use Beapp\RepositoryTesterBundle\Exception\BuildParamException;
use Beapp\RepositoryTesterBundle\Service\ClassParser;
use Beapp\RepositoryTesterBundle\Service\ParamBuilder;
use Beapp\RepositoryTesterBundle\Service\TestReporter;
use Beapp\RepositoryTesterBundle\Test\MethodTester;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\Query\QueryException;
use Error;
use Psr\Log\LoggerInterface;
use TypeError;

class RepositoryTester
{
    /** @var LoggerInterface */
    private $logger;

    /** @var RepositoryService */
    private $repositoryService;

    /** @var TestReporter */
    private $testReporter;

    /** @var ParamBuilder */
    private $paramBuilder;

    /** @var ClassParser */
    private $classParser;

    /**
     * RepositoryTester constructor.
     * @param LoggerInterface $logger
     * @param RepositoryService $repositoryService
     * @param ParamBuilder $paramBuilder
     * @param ClassParser $classParser
     */
    public function __construct(LoggerInterface $logger, RepositoryService $repositoryService, ParamBuilder $paramBuilder, ClassParser $classParser)
    {
        $this->logger = $logger;
        $this->repositoryService = $repositoryService;
        $this->testReporter = new TestReporter();
        $this->paramBuilder = $paramBuilder;
        $this->classParser = $classParser;
    }

    /**
     * @param bool $unitTestMode
     * @return TestReporter|array
     * @throws \ReflectionException
     */
    public function crawlRepositories(bool $unitTestMode)
    {
        $this->logger->info('Start crawling repos');

        $repositories = $this->repositoryService->getRepositoriesFromMetadata();
        $methodsTesters = [];

        foreach($repositories as $repository)
        {
            $repositoryClass = get_class($repository);
            $this->testReporter->setCurrentClass($repositoryClass);
            $methods = $this->classParser->crawlMethodsFrom($repositoryClass);

            foreach($methods as $method)
            {
                $methodTester = $this->buildMethodTester($method, $repository);

                if(null !== $methodTester){
                    $methodsTesters[] = $methodTester;

                    if($unitTestMode){
                        $this->testMethod($methodTester);
                    }
                }
            }
        }

        return $unitTestMode ? $methodsTesters : $this->testReporter;
    }

    /**
     * @param \ReflectionMethod $reflectionMethod
     * @param object $objectInstance
     * @return MethodTester|null
     */
    public function buildMethodTester(\ReflectionMethod $reflectionMethod, object $objectInstance): ?MethodTester
    {
        $methodName = $reflectionMethod->name;

        try{
            $parameters = $this->paramBuilder->buildParametersForMethod($reflectionMethod);
        }catch(BuildParamException $e){
            $className = get_class($objectInstance);

            $this->testReporter->addSkippedTest($className, $methodName, $e->getMessage());
            $this->logger->notice('Unable to test method '.$methodName, ['errorMessage' => $e->getMessage()]);

            return null;
        }

        return new MethodTester($reflectionMethod, $objectInstance, $parameters);
    }

    /**
     * @param MethodTester $methodTester
     * @return array
     */
    public function testMethod(MethodTester $methodTester)
    {
        $methodName = $methodTester->getMethod()->getName();
        $className = $methodTester->getTestedClass();

        $result =  [
            'success' => true,
            'reason' => '',
        ];

        try{
            $methodTester->test();

            $this->testReporter->addSuccessTest($className);
        }catch(NoResultException|NonUniqueResultException $e){

            $this->logger->info('Exception thrown during test of method '.$methodName, ['errorMsg' => $e->getMessage()]);

        }catch(Error|TypeError|QueryException|\Exception $e){
            $result['reason'] = $this->testReporter->buildErrorText(
                $e->getMessage(),
                get_class($e),
                $methodName
            );

            $result['success'] = false;

            $this->testReporter->addErrorToReport($className, $methodName, $e->getMessage(), get_class($e));
        }

        return $result;
    }
}
