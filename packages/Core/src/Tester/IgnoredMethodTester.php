<?php

namespace Beapp\RepositoryTester\Tester;

use Beapp\RepositoryTester\Exception\IgnoredMethodTestException;
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
        throw new IgnoredMethodTestException("Method ignored: " . $this->reason);
    }

    /**
     * @return string
     */
    public function getReason(): string
    {
        return $this->reason;
    }

}