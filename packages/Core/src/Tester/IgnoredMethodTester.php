<?php

namespace Beapp\RepositoryTester\Tester;

use ReflectionMethod;

class IgnoredMethodTester extends MethodTester
{

    /** @var string */
    private $reason;

    public function __construct(ReflectionMethod $reflectionMethod, $testedInstance, string $reason)
    {
        parent::__construct($reflectionMethod, $testedInstance, []);
        $this->reason = $reason;
    }

    public function test()
    {
        // Nothing to do
    }

    /**
     * @return string
     */
    public function getReason(): string
    {
        return $this->reason;
    }

}