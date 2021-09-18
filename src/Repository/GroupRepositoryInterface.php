<?php

namespace Oxygen\Auth\Repository;

use Oxygen\Data\Repository\RepositoryInterface;

interface GroupRepositoryInterface extends RepositoryInterface {

    public function findByNickname(string $nickname);
}