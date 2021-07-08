<?php

namespace Oxygen\Auth\Permissions;

use Closure;
use InvalidArgumentException;
use RuntimeException;

/**
 * This is the canonical implementation of permissions in `oxygen/auth`.
 *
 * A single permission consists of two parts, a content type, and an action.
 *
 * e.g.: pages.getView, or events.putUpdate
 *
 * Each content type can inherit permissions from a parent content type, forming a tree of permissions.
 * Inheritance is set by specifying the "_parent" field within a given content type.
 * Access to an entire content type can be blocked by setting "_access" to false.
 * And all content types ultimately inherit from the "_root" content type.
 *
 * Furthermore, permissions can be inherited from parent groups. If a permission is not explicitly granted or
 * denied by the users' group, the system will then look at parent groups to determine whether permissions were granted or not.
 *
 * @package Oxygen\Auth\Permissions
 */
class TreePermissionsSystem implements PermissionsInterface {

    const ROOT_CONTENT_TYPE = '_root';
    const ACCESS_KEY = '_access';
    const PARENT_KEY = '_parent';
    const MAX_INHERITANCE_DEPTH = 10;

    /**
     * Gets the value for the provided permissions key.
     *
     * @param string $key the permissions key in dot notation
     * @return boolean if the permission is true or false
     */
    public function hasPermissions(Closure $permissionsGenerator, string $key): bool {
        $keyParts = explode('.', $key);

        if(count($keyParts) !== 2) {
            throw new InvalidArgumentException('SimplePermissionsSystem Requires a Dot-Seperated Permissions Key');
        }

        // check for the access key
        $canAccess = $this->checkPermissionsRecursive($permissionsGenerator, $keyParts[0], self::ACCESS_KEY);
        if($canAccess === null || $canAccess === false) {
            return false;
        }

        // check for the specific key
        $hasPermission = $this->checkPermissionsRecursive($permissionsGenerator, $keyParts[0], $keyParts[1]);
        if($hasPermission !== null) {
            return $hasPermission;
        }

        // by default deny access
        return false;
    }

    protected function getPermissionsValue(Closure $permissionsGenerator, string $contentType, string $action) {
        // dump($contentType . '.' . $action);
        $generator = $permissionsGenerator();
        foreach($generator as $permissionsArray) {
            if(isset($permissionsArray[$contentType][$action])) {
                return $permissionsArray[$contentType][$action];
            }
        }
        return null;
    }

    /**
     * Check if there is an access permission for the given key.
     *
     * @param Closure $permissionsGenerator
     * @param string $contentType
     * @param string $action
     * @param int $depth
     * @return bool
     */
    protected function checkPermissionsRecursive(Closure $permissionsGenerator, string $contentType, string $action, int $depth = 0): ?bool {
        // check we're not looping
        if($depth > self::MAX_INHERITANCE_DEPTH) {
            throw new RuntimeException('Max Depth Reached due to Inheritance Loop');
        }

        // if the key is set then we will return the value of it
        $exactMatch = $this->getPermissionsValue($permissionsGenerator, $contentType, $action);
        if($exactMatch !== null) {
            return $exactMatch;
        } else {
            $parent = $this->getPermissionsValue($permissionsGenerator, $contentType, self::PARENT_KEY);
            if($parent === null) {
                $parent = self::ROOT_CONTENT_TYPE;
            }
            if($contentType === self::ROOT_CONTENT_TYPE) {
                return null;
            }
            // look in the parent contentType
            return $this->checkPermissionsRecursive($permissionsGenerator, $parent, $action, $depth + 1);
        }
    }

}
