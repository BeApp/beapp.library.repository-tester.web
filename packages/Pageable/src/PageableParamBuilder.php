<?php

namespace Beapp\RepositoryTesterBundle\Pageable;

use Beapp\Doctrine\Pagination\AdminPageable;
use Beapp\Doctrine\Pagination\ApiPageable;
use Beapp\Doctrine\Pagination\Pageable;
use Beapp\RepositoryTesterBundle\Exception\BuildParamException;
use Beapp\RepositoryTesterBundle\Service\ParamBuilder;

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
        if(in_array($type, [ApiPageable::class, AdminPageable::class, Pageable::class])){
            return new ApiPageable(1, 10);
        }

        return parent::convertTypeIntoParam($type);
    }
}
