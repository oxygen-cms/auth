<?php

namespace Oxygen\Auth\Repository;

use Oxygen\Data\Repository\Doctrine\Repository;
use Oxygen\Auth\Entity\User;

class DoctrineUserRepository extends Repository implements UserRepositoryInterface {

    /**
     * The name of the entity.
     *
     * @var string
     */

    protected $entityName = User::class;

}
