<?php

namespace Beapp\RepositoryTesterBundle\Pageable\Tester;

use Beapp\RepositoryTester\Tester\MethodTesterFactory;
use ReflectionMethod;

class PaginationMethodTesterFactory extends MethodTesterFactory
{

    protected function buildMockMethodTester(ReflectionMethod $reflectionMethod, $objectInstance, array $parameters)
    {
        return new PaginationMockMethodTester($reflectionMethod, $objectInstance, $parameters);
    }

}