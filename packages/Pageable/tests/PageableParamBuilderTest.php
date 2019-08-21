<?php

namespace Beapp\RepositoryTesterBundle\Pageable;

use Beapp\Doctrine\Pagination\AdminPageable;
use Beapp\Doctrine\Pagination\ApiPageable;
use Beapp\Doctrine\Pagination\Pageable;
use Beapp\RepositoryTester\Exception\BuildParamException;
use Beapp\RepositoryTester\Tester\ParamBuilderTest;
use Beapp\RepositoryTesterBundle\Pageable\Internal\CustomPageable;

class PageableParamBuilderTest extends ParamBuilderTest
{

    protected function setUp(): void
    {
        parent::setUp();
        $this->paramBuilder = new PageableParamBuilder($this->logger);
    }

    /**
     * @throws BuildParamException
     */
    public function testGetDummyValueForType_pageable()
    {
        $this->assertInstanceOf(ApiPageable::class, $this->paramBuilder->getDummyValueForType(Pageable::class));

        $this->assertInstanceOf(ApiPageable::class, $this->paramBuilder->getDummyValueForType(ApiPageable::class));
        $this->assertInstanceOf(AdminPageable::class, $this->paramBuilder->getDummyValueForType(AdminPageable::class));
        $this->assertInstanceOf(CustomPageable::class, $this->paramBuilder->getDummyValueForType(CustomPageable::class));
    }

}
