<?php

namespace Beapp\RepositoryTesterBundle\Service;

use Beapp\RepositoryTesterBundle\Exception\BuildParamException;
use Doctrine\ORM\QueryBuilder;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class ParamBuilderTest extends TestCase
{
    /**
     * @return array
     */
    public function typeProvider(): array
    {
        return [
            ['int'],
            ['string'],
            ['float'],
            ['bool'],
            [\DateTime::class],
            [QueryBuilder::class],
            ['unexpected']
        ];
    }

    /**
     * @return array
     * @throws \ReflectionException
     */
    public function methodProvider(): array
    {
        return [
            [new \ReflectionMethod(self::class, 'methodProvider')],
            [new \ReflectionMethod(self::class, 'testConvertTypeIntoParam')],
            [new \ReflectionMethod(self::class, 'dummyMethod')],
            [new \ReflectionMethod(self::class, 'dummyCommentedMethod')]
        ];
    }

    /**
     * Dummy comment
     */
    public function dummyMethod($stringValue)
    {
        //This method is only used for reflection test. @see methodProvider above.
    }

    /**
     * @param string|int $stringValue
     */
    public function dummyCommentedMethod($stringValue)
    {
        //This method is only used for reflection test. @see methodProvider above.
    }

    /**
     * @test
     * @dataProvider typeProvider
     *
     * @param string $paramType
     * @throws BuildParamException
     */
    public function testConvertTypeIntoParam(string $paramType)
    {
        $paramBuilder = new ParamBuilder($this->getMockedLogger());

        if(in_array($paramType, [QueryBuilder::class, 'unexpected'])){
            $this->expectException(BuildParamException::class);
        }

        $param = $paramBuilder->convertTypeIntoParam($paramType);

        if($paramType === 'int'){
            $this->assertIsInt($param);
        }elseif($paramType === 'string'){
            $this->assertIsString($param);
        }elseif($paramType === 'float'){
            $this->assertIsFloat($param);
        }elseif($paramType === 'bool'){
            $this->assertIsBool($param);
        }elseif($paramType === \DateTime::class){
            $this->assertInstanceOf(\DateTime::class, $param);
        }
    }

    /**
     * @test
     * @dataProvider methodProvider
     *
     * @param \ReflectionMethod $reflectionMethod
     */
    public function testBuildParametersForMethod(\ReflectionMethod $reflectionMethod)
    {
        $paramBuilder = new ParamBuilder($this->getMockedLogger());

        if($reflectionMethod->getName() === 'dummyMethod'){
            $this->expectException(BuildParamException::class);
        }

        $result = $paramBuilder->buildParametersForMethod($reflectionMethod);

        $this->assertIsArray($result);

        if($reflectionMethod->getName() === 'methodProvider'){
            $this->assertEmpty($result);
        }else{
            $this->assertNotEmpty($result);
        }
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

        return $logger;
    }
}
