<?php

namespace Oxygen\Auth\Permissions;
use Oxygen\Auth\Entity\User;

interface OwnedByUser {

    public function getOwner(): User;

}