<?php

namespace Beapp\RepositoryTester\Crawler;

use Doctrine\Common\Persistence\Mapping\ClassMetadata;
use Doctrine\Common\Persistence\ObjectRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Psr\Log\LoggerInterface;
use ReflectionClass;
use ReflectionMethod;

class RepositoryCrawler
{
    /** @var LoggerInterface */
    private $logger;

    /** @var EntityManagerInterface */
    private $entityManager;

    /**
     * @param LoggerInterface $logger
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(LoggerInterface $logger, EntityManagerInterface $entityManager)
    {
        $this->logger = $logger;
        $this->entityManager = $entityManager;
    }

    /**
     * @return ObjectRepository[]
     */
    public function lookupRepositoriesFromMetadata(): array
    {
        /** @var ClassMetadata[] $metadatas */
        $metadatas = $this->entityManager->getMetadataFactory()->getAllMetadata();

        $repositories = [];
        foreach ($metadatas as $metadata) {
            $reflectionClass = $metadata->getReflectionClass();
            $entityClass = $metadata->getName();
            $this->logger->info('Inspect entity ' . $entityClass);

            if ($reflectionClass->isAbstract()) {
                $this->logger->debug($entityClass . ' is abstract, skip it.');
                continue;
            }

            $repository = $this->entityManager->getRepository($entityClass);

            if (EntityRepository::class === get_class($repository)) {
                $this->logger->debug('No repository declared for entity ' . $entityClass . ', skip it.');
                continue;
            }

            $repositories[] = $repository;
        }

        return $repositories;
    }

    /**
     * @param ObjectRepository $repository
     * @return ReflectionMethod[]
     * @throws \ReflectionException
     */
    public function lookupMethodsFromRepository(ObjectRepository $repository): array
    {
        $repositoryClass = get_class($repository);
        $reflectionClass = new ReflectionClass($repositoryClass);

        $this->logger->info('Crawling methods of class : ' . $repositoryClass);

        $reflectionMethods = $reflectionClass->getMethods(ReflectionMethod::IS_PUBLIC | ReflectionMethod::IS_PROTECTED);

        // Filter out methods inherited from another class
        $reflectionMethods = array_filter($reflectionMethods, function (ReflectionMethod $reflectionMethod) use ($repositoryClass) {
            return $reflectionMethod->class === $repositoryClass;
        });

        $this->logger->debug('Found methods from class ' . $repositoryClass . ': ' . join(', ', array_map(function (ReflectionMethod $reflectionMethod) {
                return $reflectionMethod->getName();
            }, $reflectionMethods))
        );

        return $reflectionMethods;
    }
}
