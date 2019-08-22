<?php

namespace Beapp\RepositoryTester\Internal\Doctrine\Repository;

use Beapp\RepositoryTester\Internal\Doctrine\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Mapping;

class UserRepository extends EntityRepository
{

    public function __construct(EntityManagerInterface $em, Mapping\ClassMetadata $class)
    {
        parent::__construct($em, $class);
    }

    public function findById(int $id)
    {
        return $this->createQueryBuilder('u')
            ->where('u.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findByUser(User $user)
    {
        return $this->createQueryBuilder('u')
            ->where('u.id = :user')
            ->setParameter('user', $user)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @param string $name
     * @param bool $useRegexp
     * @return User
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    protected function findByName($name, bool $useRegexp = false)
    {
        return $this->createQueryBuilder('u')
            ->where('u.name = :name')
            ->setParameter('name', $name)
            ->getQuery()
            ->getOneOrNullResult();
    }

    private function somePrivateMethodWithShouldntBeTested()
    {
        // Nothing to do
    }

    public function aQueryWithUnknownField(int $email)
    {
        return $this->createQueryBuilder('u')
            ->where('u.email = :email')
            ->setParameter('email', $email)
            ->getQuery()
            ->getOneOrNullResult();
    }

}