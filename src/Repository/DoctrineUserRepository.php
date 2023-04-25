<?php

namespace Oxygen\Auth\Repository;

use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Oxygen\Data\Repository\Doctrine\Repository;
use Oxygen\Auth\Entity\User;

class DoctrineUserRepository extends Repository implements UserRepositoryInterface {

    /**
     * The name of the entity.
     *
     * @var string
     */

    protected $entityName = User::class;

    /**
     * @throws NonUniqueResultException
     * @throws NoResultException
     */
    public function findByUsername(string $username): User {
        return $this->getQuery(
            $this->createSelectQuery()->where('o.username = :username')->setParameter('username', $username)
        )->getSingleResult();
    }

}
