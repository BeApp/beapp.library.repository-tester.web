<?php

namespace Beapp\RepositoryTester\Tester;


use Beapp\RepositoryTester\Exception\MethodTestException;
use Beapp\RepositoryTester\Internal\Doctrine\Entity\User;
use Beapp\RepositoryTester\Internal\Doctrine\Repository\UserRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMInvalidArgumentException;
use Doctrine\ORM\Query\QueryException;
use Doctrine\ORM\Tools\SchemaTool;
use Doctrine\ORM\Tools\Setup;
use PHPUnit\Framework\TestCase;
use ReflectionException;
use ReflectionMethod;

class MockMethodTesterTest extends TestCase
{

    /** @var EntityManager */
    protected $entityManager;
    /** @var UserRepository */
    private $userRepository;

    protected function setUp(): void
    {
        $config = Setup::createAnnotationMetadataConfiguration([__DIR__ . "/../Internal"], true, null, null, false);
        $this->entityManager = EntityManager::create(['driver' => 'pdo_sqlite', 'memory' => true], $config);

        $schemaTool = new SchemaTool($this->entityManager);
        $schemaTool->createSchema($this->entityManager->getMetadataFactory()->getAllMetadata());

        $this->userRepository = $this->entityManager->getRepository(User::class);
    }

    /**
     * @throws MethodTestException
     * @throws ReflectionException
     */
    public function testTest_validMethod_simpleParams()
    {
        $this->expectNotToPerformAssertions();
        $this->getMethodTester('findById', [1])->test();
    }

    /**
     * @throws MethodTestException
     * @throws ReflectionException
     */
    public function testTest_validMethod_validEntityParams()
    {
        $this->expectNotToPerformAssertions();

        $user = new User();
        $user->id = 1;
        $this->getMethodTester('findByUser', [$user])->test();
    }

    /**
     * @throws MethodTestException
     * @throws ReflectionException
     */
    public function testTest_validMethod_invalidEntityParams()
    {
        $this->expectException(ORMInvalidArgumentException::class);
        $this->getMethodTester('findByUser', [new User()])->test();
    }

    /**
     * @throws MethodTestException
     * @throws ReflectionException
     */
    public function testTest_invalidMethod()
    {
        $this->expectException(QueryException::class);
        $this->getMethodTester('aQueryWithUnknownField', [1])->test();
    }

    /**
     * @param string $methodName
     * @param array $parameterValues
     * @return MockMethodTester
     * @throws ReflectionException
     */
    private function getMethodTester(string $methodName, array $parameterValues)
    {
        $reflectionMethod = new ReflectionMethod(UserRepository::class, $methodName);
        return new MockMethodTester($reflectionMethod, $this->userRepository, $parameterValues);
    }
}
