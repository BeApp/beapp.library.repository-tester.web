<?php

namespace Beapp\RepositoryTesterBundle\Service\Repository;

use Beapp\RepositoryTesterBundle\Exception\BuildParamException;
use Beapp\RepositoryTesterBundle\Service\ClassParser;
use Beapp\RepositoryTesterBundle\Service\ParamBuilder;
use Beapp\RepositoryTesterBundle\Service\TestReporter;
use Beapp\RepositoryTesterBundle\Test\MethodTester;
use Doctrine\Common\Persistence\ObjectRepository;
use Error;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class RepositoryTesterTest extends TestCase
{
    /**
     * @return array
     */
    public function booleanProvider()
    {
        return [
            [true],
            [false]
        ];
    }


    /**
     * @return MethodTester[]
     * @throws \ReflectionException
     */
    public function methodProvider()
    {
        $methods = [];

        //Choose some random method, we don't need any specific one at this point
        $reflectionMethod = new \ReflectionMethod(self::class, 'methodProvider');

        $mock = $this->createMock(MethodTester::class);
        $mock->expects($this->once())
            ->method('getMethod')
            ->willReturn($reflectionMethod);

        $mock->expects($this->once())
            ->method('test')
            ->willReturn(true);

        $methods[] = [$mock];

        $mock = $this->createMock(MethodTester::class);
        $mock->expects($this->once())
            ->method('getMethod')
            ->willReturn($reflectionMethod);

        $mock->expects($this->once())
            ->method('test')
            ->willThrowException(new \Exception());

        $methods[] = [$mock];

        $mock = $this->createMock(MethodTester::class);
        $mock->expects($this->once())
            ->method('getMethod')
            ->willReturn($reflectionMethod);

        $mock->expects($this->once())
            ->method('test')
            ->willThrowException(new Error());

        $methods[] = [$mock];

        return $methods;
    }

    /**
     * @return array
     * @throws \ReflectionException
     */
    public function methodObjectProvider(): array
    {
        $objectRepository = $this->createMock(ObjectRepository::class);
        $reflectionMethod = new \ReflectionMethod($objectRepository, 'find');

        return [
          [$reflectionMethod, $objectRepository],
        ];
    }

    /**
     * @test
     * @dataProvider booleanProvider
     * @param bool $unitTestMode
     */
    public function testCrawlRepos(bool $unitTestMode)
    {
        $repositoryTester = $this->getPartiallyMockedRepositoryTester();

        $result = $repositoryTester->crawlRepositories($unitTestMode);

        if($unitTestMode){
            $this->assertIsArray($result);
            $this->assertNotEmpty($result);

            $methodTester = $result[0];
            $this->assertInstanceOf(MethodTester::class, $methodTester);
        }else{
            $this->assertInstanceOf(TestReporter::class, $result);
        }
    }

    /**
     * @test
     * @dataProvider methodProvider
     * @param MethodTester $methodTester
     */
    public function testTestMethod(MethodTester $methodTester)
    {
        $repositoryTester = $this->getRepositoryTester();

        $result = $repositoryTester->testMethod($methodTester);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('success', $result);
        $this->assertIsBool($result['success']);
        $this->assertArrayHasKey('reason', $result);
        $this->assertIsString($result['reason']);
    }

    /**
     * @test
     * @dataProvider methodObjectProvider
     *
     * @param \ReflectionMethod $reflectionMethod
     * @param object $objectInstance
     */
    public function testBuildMethodTester_success(\ReflectionMethod $reflectionMethod, object $objectInstance)
    {
        $paramBuilder = $this->createMock(ParamBuilder::class);

        $paramBuilder->expects($this->once())
                    ->method('buildParametersForMethod')
                    ->willReturn([]);


        $repositoryTester = $this->getRepositoryTester($paramBuilder);

        $result = $repositoryTester->buildMethodTester($reflectionMethod, $objectInstance);

        $this->assertInstanceOf(MethodTester::class, $result);
    }

    /**
     * @test
     * @dataProvider methodObjectProvider
     *
     * @param \ReflectionMethod $reflectionMethod
     * @param object $objectInstance
     */
    public function testBuildMethodTester_failed(\ReflectionMethod $reflectionMethod, object $objectInstance)
    {
        $paramBuilder = $this->createMock(ParamBuilder::class);

        $paramBuilder->expects($this->once())
            ->method('buildParametersForMethod')
            ->willThrowException(new BuildParamException());

        $repositoryTester = $this->getRepositoryTester($paramBuilder);

        $result = $repositoryTester->buildMethodTester($reflectionMethod, $objectInstance);

        $this->assertNull($result);
    }


    /**
     * @param ParamBuilder|null $paramBuilder
     * @return RepositoryTester
     */
    public function getRepositoryTester(ParamBuilder $paramBuilder = null): RepositoryTester
    {
        $logger = $this->getMockedLogger();
        $repositoryService = $this->getMockedRepositoryService();
        $classParser = $this->getMockedClassParser();

        if(null === $paramBuilder){
            $paramBuilder = $this->createMock(ParamBuilder::class);
        }

        return new RepositoryTester($logger, $repositoryService, $paramBuilder, $classParser);
    }

    /**
     * @return RepositoryTester
     */
    public function getPartiallyMockedRepositoryTester(): RepositoryTester
    {
        $logger = $this->getMockedLogger();
        $repositoryService = $this->getMockedRepositoryService();
        $classParser = $this->getMockedClassParser();
        $paramBuilder = $this->createMock(ParamBuilder::class);

        /** @var RepositoryTester|MockObject $repositoryTester */
        $repositoryTester = $this->getMockBuilder(RepositoryTester::class)
                                ->setConstructorArgs([$logger, $repositoryService, $paramBuilder, $classParser])
                                ->setMethods(['buildMethodTester', 'testMethod'])
                                ->getMock();

        $repositoryTester->expects($this->any())
                        ->method('testMethod');

        $repositoryTester->expects($this->any())
                        ->method('buildMethodTester')
                        ->willReturn($this->createMock(MethodTester::class));

        return $repositoryTester;
    }

    /**
     * @return LoggerInterface
     */
    public function getMockedLogger(): LoggerInterface
    {
        $logger = $this->createMock(LoggerInterface::class);

        $logger->expects($this->any())
            ->method('debug');
        $logger->expects($this->any())
            ->method('info');
        $logger->expects($this->any())
            ->method('notice');

        return $logger;
    }

    /**
     * @return RepositoryService
     */
    public function getMockedRepositoryService(): RepositoryService
    {
        $repositories = [
            $this->createMock(ObjectRepository::class)
        ];

        $repositoryService = $this->createMock(RepositoryService::class);

        $repositoryService->expects($this->any())
                        ->method('getRepositoriesFromMetadata')
                        ->willReturn($repositories);

        return $repositoryService;
    }

    /**
     * @return ClassParser
     */
    public function getMockedClassParser(): ClassParser
    {
        $methods = [
            $this->createMock(\ReflectionMethod::class)
        ];

        $classParser = $this->createMock(ClassParser::class);

        $classParser->expects($this->any())
                    ->method('crawlMethodsFrom')
                    ->willReturn($methods);

        return $classParser;
    }
}
