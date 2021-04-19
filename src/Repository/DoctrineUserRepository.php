<?php

namespace Oxygen\Auth\Repository;

use Oxygen\Data\Repository\Doctrine\Repository;

class DoctrineUserRepository extends Repository implements UserRepositoryInterface {

    /**
     * The name of the entity.
     *
     * @var string
     */

    protected $entityName = 'Oxygen\Auth\Entity\User';

}
