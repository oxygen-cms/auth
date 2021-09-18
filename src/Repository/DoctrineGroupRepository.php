<?php

namespace Oxygen\Auth\Repository;

use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException as DoctrineNoResultException;
use Oxygen\Auth\Entity\Group;
use Oxygen\Data\Repository\Doctrine\Repository;

class DoctrineGroupRepository extends Repository implements GroupRepositoryInterface {

    /**
     * The name of the entity.
     *
     * @var string
     */

    protected $entityName = Group::class;

    /**
     * @throws NonUniqueResultException
     */
    public function findByNickname(string $nickname) {
        $qb = $this->createSelectQuery()
            ->andWhere('o.nickname = :nickname')
            ->setParameter('nickname', $nickname);
        $q = $qb->getQuery();

        try {
            return $q->getSingleResult();
        } catch(DoctrineNoResultException $e) {
            throw $this->makeNoResultException($e, $q);
        }
    }
}
