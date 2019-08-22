<?php

namespace Beapp\RepositoryTester\Tester\Internal;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class SimpleEntity
{

    /**
     * @var int
     * @ORM\Id
     * @ORM\Column(type="integer")
     */
    public $id;

}