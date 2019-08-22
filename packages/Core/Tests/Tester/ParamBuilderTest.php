<?php

namespace Beapp\RepositoryTester\Tester;

use Beapp\RepositoryTester\Exception\BuildParamException;
use Beapp\RepositoryTester\Exception\NonInstantiableTypeException;
use Beapp\RepositoryTester\Exception\NoTypeFoundException;
use Beapp\RepositoryTester\Exception\UnknownTypeException;
use Beapp\RepositoryTester\Internal\Logger\ConsoleLogger;
use Beapp\RepositoryTester\Tester\Internal\MultipleMethods;
use Beapp\RepositoryTester\Tester\Internal\NonEmptyConstructor;
use Beapp\RepositoryTester\Tester\Internal\SimpleEntity;
use Beapp\RepositoryTester\Tester\Internal\SimpleObject;
use DateTime;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMException;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\Tools\Setup;
use Exception;
use PHPUnit\Framework\TestCase;
use ReflectionException;
use ReflectionMethod;

class ParamBuilderTest extends TestCase
{
    /** @var ConsoleLogger */
    protected $logger;
    /** @var EntityManager */
    protected $entityManager;
    /** @var ParamBuilder */
    protected $paramBuilder;

    /**
     * @throws ORMException
     */
    protected function setUp(): void
    {
        $this->logger = new ConsoleLogger();

        $config = Setup::createAnnotationMetadataConfiguration([__DIR__ . "/Internal"], true, null, null, false);
        $this->entityManager = EntityManager::create(['driver' => 'pdo_sqlite', 'memory' => true], $config);

        $this->paramBuilder = new ParamBuilder($this->logger, $this->entityManager);
    }

    /**
     * @throws BuildParamException
     */
    public function testGetDummyValueForType_unexpected()
    {
        $this->expectException(UnknownTypeException::class);
        $this->paramBuilder->getDummyValueForType('unexpected');
    }

    /**
     * @throws BuildParamException
     */
    public function testGetDummyValueForType_nonInstantiableObject()
    {
        $this->expectException(NonInstantiableTypeException::class);
        $this->assertInstanceOf(NonEmptyConstructor::class, $this->paramBuilder->getDummyValueForType(NonEmptyConstructor::class));
    }

    /**
     * @throws BuildParamException
     */
    public function testGetDummyValueForType_entity()
    {
        $result = $this->paramBuilder->getDummyValueForType(SimpleEntity::class);
        $this->assertInstanceOf(SimpleEntity::class, $result);
        $this->assertIsInt($result->id);
    }

    /**
     * @throws BuildParamException
     */
    public function testGetDummyValueForType()
    {
        $this->assertIsInt($this->paramBuilder->getDummyValueForType('int'));
        $this->assertIsString($this->paramBuilder->getDummyValueForType('string'));
        $this->assertIsFloat($this->paramBuilder->getDummyValueForType('float'));
        $this->assertIsBool($this->paramBuilder->getDummyValueForType('bool'));

        $this->assertInstanceOf(DateTime::class, $this->paramBuilder->getDummyValueForType(DateTime::class));
        $this->assertInstanceOf(Exception::class, $this->paramBuilder->getDummyValueForType(Exception::class));
        $this->assertInstanceOf(Expr::class, $this->paramBuilder->getDummyValueForType(Expr::class));
    }

    /**
     * @throws BuildParamException
     * @throws ReflectionException
     */
    public function testGetParametersDummyValuesForMethod_noTypeDefined()
    {
        $this->expectException(NoTypeFoundException::class);
        $this->paramBuilder->getParametersDummyValuesForMethod(new ReflectionMethod(MultipleMethods::class, 'noTypeDefined'));
    }

    /**
     * @throws BuildParamException
     * @throws ReflectionException
     */
    public function testGetParametersDummyValuesForMethod()
    {
        $this->assertSame(['value' => 'foo'], $this->paramBuilder->getParametersDummyValuesForMethod(new ReflectionMethod(MultipleMethods::class, 'typeInCodeOnly')));
        $this->assertSame(['value' => 'foo'], $this->paramBuilder->getParametersDummyValuesForMethod(new ReflectionMethod(MultipleMethods::class, 'typeInCommentOnly')));
        $this->assertSame(['value' => 'foo'], $this->paramBuilder->getParametersDummyValuesForMethod(new ReflectionMethod(MultipleMethods::class, 'typeInCodeAndComment')));
        $this->assertSame(['value' => []], $this->paramBuilder->getParametersDummyValuesForMethod(new ReflectionMethod(MultipleMethods::class, 'mixedUpTypeBetweenCodeAndComment')));

        $params = $this->paramBuilder->getParametersDummyValuesForMethod(new ReflectionMethod(MultipleMethods::class, 'multipleTypes'));
        $this->assertSame(1, $params['value1']);
        $this->assertSame(SimpleObject::class, get_class($params['value2']));
        $this->assertSame(null, $params['value3']);
        $this->assertArrayNotHasKey('value4', $params);
    }

}
