<?php

namespace Beapp\RepositoryTesterBundle\Test;

use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\Query\QueryException;

class MethodTester
{
    /** @var \ReflectionMethod */
    private $reflectionMethod;

    /** @var object */
    private $testedInstance;

    /** @var array */
    private $parameters;

    /**
     * MethodTester constructor.
     * @param \ReflectionMethod $reflectionMethod
     * @param object $testedInstance
     * @param array $parameters
     */
    public function __construct(\ReflectionMethod $reflectionMethod, object $testedInstance, array $parameters)
    {
        $this->reflectionMethod = $reflectionMethod;
        $this->testedInstance = $testedInstance;
        $this->parameters = $parameters;
    }

    /**
     * @return \ReflectionMethod
     */
    public function getMethod(): \ReflectionMethod
    {
        return $this->reflectionMethod;
    }

    /**
     * @return object
     */
    public function getTestedInstance(): object
    {
        return $this->testedInstance;
    }

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
     * @throws NoResultException|NonUniqueResultException|\Error|\TypeError|QueryException|\Exception
     */
    public function test()
    {
        return $this->reflectionMethod->invokeArgs($this->testedInstance, $this->parameters);
    }
}
