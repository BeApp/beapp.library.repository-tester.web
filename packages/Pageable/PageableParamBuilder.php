<?php

namespace Beapp\Tester\Repository\Pageable;

use Beapp\Doctrine\Pagination\ApiPageable;
use Beapp\Doctrine\Pagination\Pageable;
use Beapp\Tester\Repository\ParamBuilder;
use Beapp\Tester\Repository\Exception\BuildParamException;

class PageableParamBuilder extends ParamBuilder
{
    /**
     * Add support of Beapp pageable class
     *
     * @param string $type
     * @return array|ApiPageable|bool|\DateTime|float|int|string
     * @throws BuildParamException
     */
    public function convertTypeIntoParam(string $type)
    {
        if($type === Pageable::class){
            return new ApiPageable(1, 10);
        }

        return parent::convertTypeIntoParam($type);
    }
}
