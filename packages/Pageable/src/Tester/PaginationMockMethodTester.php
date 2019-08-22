<?php

namespace Beapp\RepositoryTesterBundle\Pageable\Tester;

use Beapp\Doctrine\Pagination\Pagination;
use Beapp\RepositoryTester\Tester\MockMethodTester;

class PaginationMockMethodTester extends MockMethodTester
{

    public function test()
    {
        $result = parent::test();

        //We must call methods of Pagination object to try to execute the query
        if ($result instanceof Pagination) {
            $result->count();
        }

        return $result;
    }

}