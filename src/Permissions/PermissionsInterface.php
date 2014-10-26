<?php

namespace Oxygen\Auth\Permissions;

interface PermissionsInterface {

    /**
     * Returns if the implementation still
     * needs for permissions to be provided.
     *
     * @return bool     if the implementation needs permissions
     */

    public function needsPermissions();

    /**
     * Sets the raw permissions array to be used
     * for all subsequent permissions checks.
     *
     * @param array     $permissions
     */

    public function setPermissions(array $permissions);

    /**
     * Gets the value for the provided permissions key.
     *
     * @param string    $key the permissions key in dot notation
     * @return bool     if the permission is true or false
     */

    public function hasPermissions($key);

}