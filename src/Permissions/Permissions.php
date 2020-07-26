<?php

namespace Oxygen\Auth\Permissions;

use RuntimeException;

trait Permissions {

    /**
     * Permissions Interface;
     *
     * @var \Oxygen\Auth\Permissions\PermissionsInterface
     */
    protected $permissionsInterface;

    /**
     * Decode the permissions and return the array.
     * Should be called only once when the PermissionsInterface is configured.
     *
     * @return array
     */
    public function decodePermissions() {
        return $this->group->getPermissions();
    }

    /**
     * Check if the user has permissions for the given key.
     *
     * @param string $key
     * @return boolean
     */
    public function hasPermissions($key) {
        if($this->permissionsInterface === null) {
            $this->permissionsInterface = resolve(PermissionsInterface::class);
        }

        if($this->permissionsInterface->needsPermissions()) {
            $this->permissionsInterface->setPermissions($this->decodePermissions());
        }
        return $this->permissionsInterface->hasPermissions($key);
    }

}