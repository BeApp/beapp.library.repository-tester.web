<?php

namespace Beapp\RepositoryTesterBundle\Pageable;

use Beapp\Doctrine\Pagination\ApiPageable;
use Beapp\Doctrine\Pagination\Pageable;
use Beapp\RepositoryTester\Tester\ParamBuilder;

class PageableParamBuilder extends ParamBuilder
{

    /**
     * Add support of Beapp pageable class
     *
     * @inheritDoc
     */
    public function getDummyValueForType(string $type)
    {
        if ($type === Pageable::class) {
            return new ApiPageable(1, 10);
        }

        return parent::getDummyValueForType($type);
    }
}
