<?php

namespace Oxygen\Auth\Repository;

use Oxygen\Auth\Entity\User;
use Oxygen\Data\Repository\RepositoryInterface;

interface UserRepositoryInterface extends RepositoryInterface {

    public function findByUsername(string $username): User;

}