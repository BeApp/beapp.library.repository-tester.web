<?php

namespace Beapp\RepositoryTester\Tester;

use Beapp\RepositoryTester\Exception\IgnoredMethodTestException;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;

class MockMethodTester extends MethodTester
{

    /**
     * @inheritDoc
     */
    public function test()
    {
        try {
            return $this->reflectionMethod->invokeArgs($this->testedInstance, $this->parameters);
        } catch (NoResultException|NonUniqueResultException $e) {
            throw new IgnoredMethodTestException("Runtime exception while executing repository method", 0, $e);
        }
    }

}