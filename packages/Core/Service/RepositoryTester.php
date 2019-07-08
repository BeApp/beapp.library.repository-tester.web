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

    /** @var bool */
    private $unitTestMode = false;

    /**
     * RepositoryTester constructor.
     * @param LoggerInterface $logger
     * @param EntityManagerInterface $entityManager
     * @param ParamBuilder $paramBuilder
     */
    public function __construct(LoggerInterface $logger, EntityManagerInterface $entityManager, ParamBuilder $paramBuilder)
    {
        $this->logger = $logger;
        $this->entityManager = $entityManager;
        $this->testReporter = new TestReporter();
        $this->paramBuilder = $paramBuilder;
    }

    /**
     * @return TestReporter|array
     * @throws \ReflectionException
     */
    public function crawlRepositories()
    {
        $this->logger->info('Start crawling repos');

        $metadatas = $this->entityManager->getMetadataFactory()->getAllMetadata();

        $methodsTesters = $this->crawlClasses($metadatas);

        return $this->unitTestMode ? $methodsTesters : $this->testReporter;
    }

    /**
     * @param bool $unitTestMode
     */
    public function setUnitTestMode(bool $unitTestMode)
    {
        $this->unitTestMode = $unitTestMode;
    }

    /**
     * @param ClassMetadata[] $entitiesMetadata
     * @return MethodTester[]
     */
    public function crawlClasses(array $entitiesMetadata): array
    {
        $methodsTesters = [];

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
            $repositoryClass = get_class($repository);

            if(EntityRepository::class === $repositoryClass){
                $this->logger->debug('No repository declared for entity '.$entityClass.', skip it.');
                continue;
            }

            $methods = get_class_methods($repositoryClass);

            $methodsTesters = array_merge($methodsTesters, $this->crawlMethods($methods, $repositoryClass, $repository));
        }

        return $methodsTesters;
    }

    /**
     * @param array $methods
     * @param string $className
     * @param ObjectRepository $repositoryInstance
     * @return MethodTester[]
     * @throws \ReflectionException
     */
    public function crawlMethods(array $methods, string $className, ObjectRepository $repositoryInstance): array
    {
        $testers = [];
        $this->testReporter->setCurrentClass($className);
        $this->logger->notice('Test methods of repository : '.$className);

        foreach($methods as $method){
            $this->logger->debug('Method name : '.$method);
            $reflectionMethod = new \ReflectionMethod($className, $method);

            if($reflectionMethod->class !== $className){
                $this->logger->debug('Method '.$method.' is herited from another class, skip it.');
                continue;
            }elseif(!$reflectionMethod->isPublic()){
                $this->logger->debug('Method '.$method.' isn\'t public, skip it.');
                continue;
            }

            $reflectionParameters = $reflectionMethod->getParameters();

            if(empty($reflectionParameters)){
                $this->logger->debug('Method '.$method.' has no parameters');
            }

            try{
                $parameters = $this->paramBuilder->buildParameters($reflectionParameters, $reflectionMethod);
            }catch(BuildParamException $e){
                if(!$this->unitTestMode){
                    $this->testReporter->addSkippedTest($method, $e->getMessage());
                }
                $this->logger->notice('Unable to test method '.$method, ['errorMessage' => $e->getMessage()]);

                continue;
            }

            $methodTester = new MethodTester($reflectionMethod, $repositoryInstance, $parameters);
            $testers[] = $methodTester;

            if(!$this->unitTestMode){
                $this->testMethod($methodTester);
            }
        }

        return $testers;
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
