<?php

namespace Beapp\Tester\Repository;

use Beapp\Tester\Repository\Exception\BuildParamException;
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

    /** @var array */
    private $methodsData = [];

    /** @var bool */
    private $unitTestMode = false;

    /**
     * RepositoryTester constructor.
     * @param LoggerInterface $logger
     * @param EntityManagerInterface $entityManager
     * @param TestReporter $testReporter
     * @param ParamBuilder $paramBuilder
     */
    public function __construct(LoggerInterface $logger, EntityManagerInterface $entityManager, TestReporter $testReporter, ParamBuilder $paramBuilder)
    {
        $this->logger = $logger;
        $this->entityManager = $entityManager;
        $this->testReporter = $testReporter;
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

        $this->crawlClasses($metadatas);

        return $this->unitTestMode ? $this->methodsData : $this->testReporter;
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
     */
    public function crawlClasses(array $entitiesMetadata)
    {
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

            $this->crawlMethods($methods, $repositoryClass, $repository);
        }
    }

    /**
     * @param array $methods
     * @param string $className
     * @param ObjectRepository $repositoryInstance
     * @throws \ReflectionException
     */
    public function crawlMethods(array $methods, string $className, ObjectRepository $repositoryInstance)
    {
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

            if($this->unitTestMode){
                $this->methodsData[] = [$reflectionMethod, $repositoryInstance, $parameters];
            }else{
                $this->testMethod($reflectionMethod, $repositoryInstance, $parameters);
            }
        }
    }

    /**
     * @param \ReflectionMethod $method
     * @param ObjectRepository $repositoryInstance
     * @param array $params
     */
    public function testMethod(\ReflectionMethod $method, ObjectRepository $repositoryInstance, array $params = []): void
    {
        try{
            $this->invokeMethod($method, $repositoryInstance, $params);
        }catch(NoResultException|NonUniqueResultException $e){
            $this->logger->info('Exception thrown during test of method '.$method->name, ['errorMsg' => $e->getMessage()]);
        }catch(Error|TypeError|QueryException|\Exception $e){
            $this->testReporter->addErrorTest($e->getMessage(), get_class($e), $method->getName());
        }

        $this->testReporter->addSuccessTest();
    }

    /**
     * @param \ReflectionMethod $method
     * @param ObjectRepository $repositoryInstance
     * @param array $params
     * @return array
     */
    public function unitaryTestMethod(\ReflectionMethod $method, ObjectRepository $repositoryInstance, array $params = []): array
    {
        try{
            $this->invokeMethod($method, $repositoryInstance, $params);
        }catch(NoResultException|NonUniqueResultException $e){
            $this->logger->info('Exception thrown during test of method '.$method->name, ['errorMsg' => $e->getMessage()]);
        }catch(Error|TypeError|QueryException|\Exception $e){
            return ['success' => false, 'reason' => $this->testReporter->buildErrorText($e->getMessage(), get_class($e), $method->name)];
        }

       return ['success' => true, 'reason' => ''];
    }

    /**
     * @param \ReflectionMethod $method
     * @param ObjectRepository $repositoryInstance
     * @param array $params
     * @throws NoResultException|NonUniqueResultException|\Error|\TypeError|QueryException|\Exception
     */
    protected function invokeMethod(\ReflectionMethod $method, ObjectRepository $repositoryInstance, array $params)
    {
        $this->logger->notice('Test of method '.$method->getName());
        $method->invokeArgs($repositoryInstance, $params);
    }
}
