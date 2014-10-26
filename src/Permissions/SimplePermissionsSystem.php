<?php

namespace Oxygen\Auth\Permissions;

use InvalidArgumentException;
use RuntimeException;

class SimplePermissionsSystem implements PermissionsInterface {

    const ROOT_CONTENT_TYPE = '_root';
    const ACCESS_KEY = '_access';
    const PARENT_KEY = '_parent';
    const MAX_INHERITANCE_DEPTH = 10;

    /**
     * Raw permissions array
     *
     * @var array
     */

    protected $permissions;

    /**
     * Returns if the implementation still
     * needs for permissions to be provided.
     *
     * @return bool     if the implementation needs permissions
     */

    public function needsPermissions() {
        return ($this->permissions === null);
    }

    /**
     * Sets the raw permissions array to be used
     * for all subsequent permissions checks.
     *
     * @param array $permissions
     */

    public function setPermissions(array $permissions) {
        $this->permissions = $permissions;
    }

    /**
     * Gets the value for the provided permissions key.
     *
     * @param string $key the permissions key in dot notation
     * @return boolean if the permission is true or false
     */

    public function hasPermissions($key) {
        $keyParts = explode('.', $key);

        if(count($keyParts) < 2) {
            throw new InvalidArgumentException('SimplePermissionsSystem Requires a Dot-Seperated Permissions Key');
        }

        // check for the access key
        if(!$this->hasPermissionsKey($keyParts[0], self::ACCESS_KEY)) {
            return false;
        }

        // check for the specific key
        return $this->hasPermissionsKey($keyParts[0], $keyParts[1]);
    }

    /**
     * Check if there is an access permission for the given key.
     *
     * @param string $contentType
     * @param string $key
     * @return bool
     */

    protected function hasPermissionsKey($contentType, $key, $depth = 0) {
        // if the key is set then we will return the value of it
        if(isset($this->permissions[$contentType][$key])) {
            $result = $this->permissions[$contentType][$key];
            return $result;
        } else if(isset($this->permissions[$contentType][self::PARENT_KEY])) {
            // check we're not looping
            if($depth > self::MAX_INHERITANCE_DEPTH) {
                throw new RuntimeException('Max Depth Reached due to Inheritance Loop');
            }
            // look in the parent contentType
            $parent = $this->permissions[$contentType][self::PARENT_KEY];
            $result = $this->hasPermissionsKey($parent, $key, $depth + 1);
            return $result;
        } else if(isset($this->permissions[self::ROOT_CONTENT_TYPE][$key])) {
            // return the root content type
            $result = $this->permissions[self::ROOT_CONTENT_TYPE][$key];
            return $result;
        } else {
            return false;
        }
    }

}