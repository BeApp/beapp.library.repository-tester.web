<?php

namespace Beapp\RepositoryTester\Tester;

use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\Query\QueryException;
use Exception;
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
     * @throws NoResultException|NonUniqueResultException|QueryException|Exception
     */
    public abstract function test();
}
