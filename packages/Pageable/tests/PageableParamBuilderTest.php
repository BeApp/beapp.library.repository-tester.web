<?php

namespace Beapp\RepositoryTesterBundle\Pageable;

use Beapp\Doctrine\Pagination\AdminPageable;
use Beapp\Doctrine\Pagination\ApiPageable;
use Beapp\Doctrine\Pagination\Pageable;
use Beapp\RepositoryTesterBundle\Exception\BuildParamException;
use Beapp\RepositoryTesterBundle\Service\ParamBuilderTest;
use Doctrine\ORM\QueryBuilder;

class PageableParamBuilderTest extends ParamBuilderTest
{
    /**
     * Extended provider from ParamBuilderTest
     *
     * @return array
     */
    public function typeProvider(): array
    {
        $originalTypes = parent::typeProvider();

        $originalTypes[] = [ApiPageable::class];
        $originalTypes[] = [AdminPageable::class];
        $originalTypes[] = [Pageable::class];

        return $originalTypes;
    }

    /**
     * @test
     * @dataProvider typeProvider
     *
     * @param string $paramType
     * @throws BuildParamException
     */
    public function testConvertTypeIntoParam(string $paramType)
    {
        $paramBuilder = new PageableParamBuilder($this->getMockedLogger());

        if(in_array($paramType, [QueryBuilder::class, 'unexpected'])){
            $this->expectException(BuildParamException::class);
        }

        $param = $paramBuilder->convertTypeIntoParam($paramType);

        if($paramType === 'int'){
            $this->assertIsInt($param);
        }elseif($paramType === 'string'){
            $this->assertIsString($param);
        }elseif($paramType === 'float'){
            $this->assertIsFloat($param);
        }elseif($paramType === 'bool'){
            $this->assertIsBool($param);
        }elseif($paramType === \DateTime::class){
            $this->assertInstanceOf(\DateTime::class, $param);
        }elseif(in_array($paramType, [ApiPageable::class, AdminPageable::class, Pageable::class])){
            $this->assertInstanceOf(ApiPageable::class, $param);
        }
    }
}
