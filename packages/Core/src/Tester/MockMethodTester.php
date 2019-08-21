<?php

namespace Beapp\RepositoryTester\Tester;

class MockMethodTester extends MethodTester
{

    /**
     * @inheritDoc
     */
    public function test()
    {
        return $this->reflectionMethod->invokeArgs($this->testedInstance, $this->parameters);
    }

}