<?php

namespace Oxygen\Auth\Permissions;

use Closure;

interface PermissionsInterface {

    /**
     * Gets the value for the provided permissions key.
     *
     * @param Closure   $permissionsGenerator a generator which returns permissions arrays to check, in reverse-order of inheritance
     * @param string    $key the permissions key in dot notation
     * @return bool     if the permission is true or false
     */
    public function hasPermissions(Closure $permissionsGenerator, string $key): bool;

}
