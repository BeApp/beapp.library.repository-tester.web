<?php

namespace Beapp\RepositoryTester\Internal\Doctrine\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Dummy entity with no repository
 *
 * @ORM\Entity
 */
class EntityWithoutRepository
{

    /**
     * @var int
     * @ORM\Id
     * @ORM\Column(type="integer")
     */
    public $id;

}