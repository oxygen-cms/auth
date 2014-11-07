<?php

namespace Oxygen\Auth\Repository;

use Doctrine\ORM\EntityManagerInterface;
use Oxygen\Data\Pagination\PaginationService;
use Oxygen\Data\Repository\Doctrine\Repository;
use Oxygen\Data\Repository\Doctrine\SoftDeletes;

class DoctrineGroupRepository extends Repository implements GroupRepositoryInterface {

    use SoftDeletes;

    /**
     * The name of the entity.
     *
     * @var string
     */

    protected $entityName = 'Oxygen\Auth\Entity\Group';

}