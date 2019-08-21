<?php

namespace Beapp\RepositoryTesterBundle\Pageable\Internal;

use Beapp\Doctrine\Pagination\Pageable;

class CustomPageable extends Pageable
{
    /** @var string */
    public $key;

    public function __construct(string $key, int $page, int $size, array $orders = [], array $search = [])
    {
        parent::__construct($page, $size, $orders, $search);
        $this->key = $key;
    }
}