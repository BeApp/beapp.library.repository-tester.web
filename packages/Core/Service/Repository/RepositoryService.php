<?php

namespace Beapp\RepositoryTesterBundle\Service\Repository;

use Doctrine\Common\Persistence\ObjectRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Psr\Log\LoggerInterface;

class RepositoryService
{
    /** @var EntityManagerInterface */
    private $entityManager;

    /** @var LoggerInterface */
    private $logger;

    /**
     * RepositoryService constructor.
     * @param EntityManagerInterface $entityManager
     * @param LoggerInterface $logger
     */
    public function __construct(EntityManagerInterface $entityManager, LoggerInterface $logger)
    {
        $this->entityManager = $entityManager;
        $this->logger = $logger;
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
}
