<?php

namespace Beapp\RepositoryTester\Crawler;

use Beapp\RepositoryTester\Internal\Doctrine\Entity\User;
use Beapp\RepositoryTester\Internal\Doctrine\Repository\UserRepository;
use Beapp\RepositoryTester\Internal\Logger\ConsoleLogger;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMException;
use Doctrine\ORM\Tools\Setup;
use PHPUnit\Framework\TestCase;
use ReflectionException;
use ReflectionMethod;

class RepositoryCrawlerTest extends TestCase
{

    /** @var ConsoleLogger */
    private $logger;
    /** @var EntityManager */
    private $entityManager;
    /** @var RepositoryCrawler */
    private $repositoryCrawler;

    /**
     * @throws ORMException
     */
    public function setUp(): void
    {
        $this->logger = new ConsoleLogger();

        $config = Setup::createAnnotationMetadataConfiguration([__DIR__ . "/../Internal/Doctrine"], true, null, null, false);
        $this->entityManager = EntityManager::create(['driver' => 'pdo_sqlite', 'memory' => true], $config);

        $this->repositoryCrawler = new RepositoryCrawler($this->logger, $this->entityManager);
    }

    public function testLookupRepositoriesFromMetadata()
    {
        $objectRepositories = $this->repositoryCrawler->lookupRepositoriesFromMetadata();

        $this->assertCount(1, $objectRepositories);
        $this->assertSame(UserRepository::class, get_class($objectRepositories[0]));
    }

    /**
     * @throws ReflectionException
     */
    public function testLookupMethodsFromRepository()
    {
        $userRepository = $this->entityManager->getRepository(User::class);
        $reflectionMethods = $this->repositoryCrawler->lookupMethodsFromRepository($userRepository);

        $this->assertCount(3, $reflectionMethods);

        $methodNames = array_map(function (ReflectionMethod $reflectionMethod) {
            return $reflectionMethod->name;
        }, $reflectionMethods);
        sort($methodNames);
        $this->assertEquals(['aQueryWithUnknownField', 'findById', 'findByName'], $methodNames);
    }

}
