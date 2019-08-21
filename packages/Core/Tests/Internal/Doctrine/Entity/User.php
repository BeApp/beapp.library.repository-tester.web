<?php

namespace Beapp\RepositoryTester\Internal\Doctrine\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="Beapp\RepositoryTester\Internal\Doctrine\Repository\UserRepository")
 */
class User
{

    /**
     * @var int
     * @ORM\Id
     * @ORM\Column(type="integer")
     */
    public $id;

    /**
     * @var string
     * @ORM\Column(type="string")
     */
    public $name;

}