<?php

namespace Beapp\RepositoryTesterBundle\Service;

use Beapp\RepositoryTesterBundle\Exception\BuildParamException;
use Beapp\RepositoryTesterBundle\Test\MethodTester;
use Doctrine\Common\Persistence\ObjectRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Mapping\ClassMetadata;
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

    /** @var EntityManagerInterface */
    private $entityManager;

    /** @var TestReporter */
    private $testReporter;

    /** @var ParamBuilder */
    private $paramBuilder;

    /** @var ClassParser */
    private $classParser;

    /**
     * RepositoryTester constructor.
     * @param LoggerInterface $logger
     * @param EntityManagerInterface $entityManager
     * @param ParamBuilder $paramBuilder
     * @param ClassParser $classParser
     */
    public function __construct(LoggerInterface $logger, EntityManagerInterface $entityManager, ParamBuilder $paramBuilder, ClassParser $classParser)
    {
        $this->logger = $logger;
        $this->entityManager = $entityManager;
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

        $repositories = $this->getRepositoriesFromMetadata();
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
     * @return ObjectRepository[]
     */
    public function getRepositoriesFromMetadata(): array
    {
        $repositories = [];
        $entitiesMetadata = $this->entityManager->getMetadataFactory()->getAllMetadata();

        foreach($entitiesMetadata as $entityMetadata)
        {
            $reflectionClass = $entityMetadata->getReflectionClass();
            $entityClass = $entityMetadata->getName();
            $this->logger->notice('Inspect entity '.$entityClass);

            if($reflectionClass->isAbstract()){
                $this->logger->debug($entityClass.' is abstract, skip it.');
                continue;
            }

            $repository = $this->entityManager->getRepository($entityClass);

            if(EntityRepository::class === get_class($repository)){
                $this->logger->debug('No repository declared for entity '.$entityClass.', skip it.');
                continue;
            }

            $repositories[] = $repository;
        }

        return $repositories;
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
            $this->testReporter->addSkippedTest($methodName, $e->getMessage());
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
        $result =  [
            'success' => true,
            'reason' => '',
        ];

        try{
            $methodTester->test();

            $this->testReporter->addSuccessTest();
        }catch(NoResultException|NonUniqueResultException $e){

            $this->logger->info('Exception thrown during test of method '.$methodName, ['errorMsg' => $e->getMessage()]);

        }catch(Error|TypeError|QueryException|\Exception $e){
            $result['reason'] = $this->testReporter->buildErrorText(
                $e->getMessage(),
                get_class($e),
                $methodName
            );

            $result['success'] = false;

            $this->testReporter->addErrorToReport($e->getMessage(), get_class($e), $methodName);
        }

        return $result;
    }
}
