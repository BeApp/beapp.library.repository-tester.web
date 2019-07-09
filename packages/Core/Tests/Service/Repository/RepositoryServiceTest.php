<?php

namespace Beapp\RepositoryTesterBundle\Service\Repository;

use Doctrine\Common\Persistence\Mapping\AbstractClassMetadataFactory;
use Doctrine\Common\Persistence\Mapping\ClassMetadata;
use Doctrine\Common\Persistence\Mapping\ClassMetadataFactory;
use Doctrine\Common\Persistence\ObjectRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use ReflectionClass;

class RepositoryServiceTest extends TestCase
{
    /**
     * Provides a list of dummy classes names
     *
     * @return array
     */
    public function classProvider()
    {
        return [
            [EntityRepository::class],
            [AbstractClassMetadataFactory::class],
        ];
    }

    /**
     * @test
     * @dataProvider classProvider
     * @param string $className
     * @throws \ReflectionException
     */
    public function testGetRepositoriesFromMetadata(string $className)
    {
        $reflectionClass = new ReflectionClass($className);
        $repositoryService = $this->getRepositoryService($reflectionClass);

        $result = $repositoryService->getRepositoriesFromMetadata();

        $this->assertIsArray($result);


        if($reflectionClass->isAbstract()){
            $this->assertEmpty($result);
        }else{
            $this->assertNotEmpty($result);
            $this->assertInstanceOf(ObjectRepository::class, $result[0]);
        }
    }

    /**
     * @param ReflectionClass $reflectionClass
     * @return RepositoryService
     */
    public function getRepositoryService(ReflectionClass $reflectionClass): RepositoryService
    {
        $logger = $this->createMock(LoggerInterface::class);

        $logger->expects($this->any())
                ->method('debug');
        $logger->expects($this->any())
                ->method('notice');

        $entityManager = $this->getMockedEntityManager($reflectionClass);

        return new RepositoryService($entityManager, $logger);
    }

    /**
     * @param ReflectionClass $reflectionClass
     * @return EntityManagerInterface
     */
    public function getMockedEntityManager(ReflectionClass $reflectionClass): EntityManagerInterface
    {
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $metadataFactoryMock = $this->createMock(ClassMetadataFactory::class);
        $classMetadataMock = $this->createMock(ClassMetadata::class);
        $repositoryMock = $this->createMock(ObjectRepository::class);

        $classMetadataMock->expects($this->once())
                        ->method('getReflectionClass')
                        ->willReturn($reflectionClass);

        $classMetadataMock->expects($this->once())
                        ->method('getName')
                        ->willReturn('foo');

        $metadataFactoryMock->expects($this->once())
                            ->method('getAllMetadata')
                            ->willReturn([$classMetadataMock]);

        $entityManager->expects($this->once())
            ->method('getMetadataFactory')
            ->willReturn($metadataFactoryMock);

        $entityManager->expects($this->any())
                        ->method('getRepository')
                        ->willReturn($repositoryMock);

        return $entityManager;
    }
}
