<?php

namespace Beapp\RepositoryTester\Tester;

use Beapp\RepositoryTester\Exception\BuildParamException;
use Psr\Log\LoggerInterface;
use ReflectionMethod;

class MethodTesterFactory
{

    /** @var LoggerInterface */
    private $logger;
    /** @var ParamBuilder */
    private $paramBuilder;

    public function __construct(LoggerInterface $logger, ParamBuilder $paramBuilder)
    {
        $this->logger = $logger;
        $this->paramBuilder = $paramBuilder;
    }

    /**
     * @param ReflectionMethod $reflectionMethod
     * @param object $objectInstance
     * @return MethodTester
     */
    public function buildMethodTester(ReflectionMethod $reflectionMethod, $objectInstance): MethodTester
    {
        $methodName = $reflectionMethod->name;

        try {
            $parameters = $this->paramBuilder->getParametersDummyValuesForMethod($reflectionMethod);
        } catch (BuildParamException $e) {
            $this->logger->warning('Unable to test method ' . $methodName, ['errorMessage' => $e->getMessage()]);

            return new IgnoredMethodTester($reflectionMethod, $objectInstance, $e->getMessage());
        }

        return new MockMethodTester($reflectionMethod, $objectInstance, $parameters);
    }

}