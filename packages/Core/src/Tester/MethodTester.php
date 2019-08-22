<?php

namespace Beapp\RepositoryTester\Tester;

use Beapp\RepositoryTester\Exception\MethodTestException;
use ReflectionMethod;

abstract class MethodTester
{
    /** @var ReflectionMethod */
    protected $reflectionMethod;

    /** @var object */
    protected $testedInstance;

    /** @var array */
    protected $parameters;

    /**
     * MethodTester constructor.
     * @param ReflectionMethod $reflectionMethod
     * @param object $testedInstance
     * @param array $parameters
     */
    public function __construct(ReflectionMethod $reflectionMethod, $testedInstance, array $parameters = [])
    {
        $this->reflectionMethod = $reflectionMethod;
        $this->testedInstance = $testedInstance;
        $this->parameters = $parameters;
    }

    /**
     * @return ReflectionMethod
     */
    public function getMethod(): ReflectionMethod
    {
        return $this->reflectionMethod;
    }

    /**
     * @return object
     */
    public function getTestedInstance()
    {
        return $this->testedInstance;
    }

    /**
     * @return string
     */
    public function getTestedClass(): string
    {
        return get_class($this->testedInstance);
    }

    /**
     * @return array
     */
    public function getParameters(): array
    {
        return $this->parameters;
    }

    /**
     * @return mixed
     * @throws MethodTestException
     */
    public abstract function test();
}
