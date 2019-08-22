<?php

namespace Beapp\RepositoryTester\Tester;


use Beapp\RepositoryTester\Exception\IgnoredMethodTestException;
use Beapp\RepositoryTester\Exception\MethodTestException;
use Beapp\RepositoryTester\Internal\Doctrine\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use PHPUnit\Framework\TestCase;
use ReflectionException;
use ReflectionMethod;

class IgnoredMethodTesterTest extends TestCase
{

    /**
     * @throws MethodTestException
     * @throws ReflectionException
     */
    public function testTest()
    {
        $reflectionMethod = new ReflectionMethod(UserRepository::class, 'somePrivateMethodWithShouldntBeTested');
        $userRepository = new UserRepository($this->createMock(EntityManagerInterface::class), $this->createMock(ClassMetadata::class));
        $ignoredMethodTester = new IgnoredMethodTester($reflectionMethod, $userRepository, '');

        $this->expectException(IgnoredMethodTestException::class);
        $ignoredMethodTester->test();
    }
}
