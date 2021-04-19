<?php

namespace Oxygen\Auth\Repository;

use Oxygen\Data\Repository\Doctrine\Repository;

class DoctrineGroupRepository extends Repository implements GroupRepositoryInterface {

    /**
     * The name of the entity.
     *
     * @var string
     */

    protected $entityName = 'Oxygen\Auth\Entity\Group';

}
