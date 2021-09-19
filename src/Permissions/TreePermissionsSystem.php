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
 * And all content types ultimately inherit from the "_root" content type.
 *
 * Furthermore, permissions can be inherited from parent groups. If a permission is not explicitly granted or
 * denied by the users' group, the system will then look at parent groups to determine whether permissions were granted or not.
 *
 * @package Oxygen\Auth\Permissions
 */
class TreePermissionsSystem implements PermissionsInterface {

    const ROOT_CONTENT_TYPE = '_root';
    const PARENT_KEY = '_parent';
    const MAX_INHERITANCE_DEPTH = 10;

    /**
     * Gets the value for the provided permissions key.
     *
     * @param Closure   $permissionsGenerator a generator which returns permissions arrays to check, in reverse-order of inheritance
     * @param string    $key the permissions key in dot notation
     * @return PermissionsExplanation     explanation of how the user has/hasn't got these permissions
     */
    public function explainPermissions(Closure $permissionsGenerator, string $key): PermissionsExplanation {
        $keyParts = explode('.', $key);

        if(count($keyParts) !== 2) {
            throw new InvalidArgumentException('SimplePermissionsSystem Requires a Dot-Seperated Permissions Key');
        }

        // check recursively
        $explanation = $this->checkPermissionsRecursive($permissionsGenerator, $keyParts[0], $keyParts[1]);
        $explanation->setRequestedInfo($this->getOriginalSource($permissionsGenerator), $keyParts[0], $keyParts[1]);
        return $explanation;
    }

    /**
     * @param Closure $permissionsGenerator
     * @param string $contentType
     * @param string $action
     * @return PermissionsExplanation|null
     */
    protected function getPermissionsValue(Closure $permissionsGenerator, string $contentType, string $action): ?PermissionsExplanation {
        $generator = $permissionsGenerator();
        foreach($generator as $permissionsSource) {
            $permissions = $permissionsSource->getPermissions();
            if(isset($permissions[$contentType][$action])) {
                $explanation = new PermissionsExplanation($permissions[$contentType][$action]);
                $explanation->setSource($permissionsSource, $contentType, $action);
                return $explanation;
            }
        }
        return null;
    }

    private function getOriginalSource(Closure $permissionsGenerator): PermissionsSource {
        $generator = $permissionsGenerator();
        foreach($generator as $item) { return $item; }
    }

    /**
     * @param Closure $permissionsGenerator
     * @param string $contentType
     * @return PermissionsExplanation
     */
    public function explainParentContentType(Closure $permissionsGenerator, string $contentType): PermissionsExplanation {
        $explanation = $this->getPermissionsValue($permissionsGenerator, $contentType, self::PARENT_KEY);
        if($explanation === null) {
            $explanation = new PermissionsExplanation(self::ROOT_CONTENT_TYPE);
        }
        $explanation->setRequestedInfo($this->getOriginalSource($permissionsGenerator), $contentType, self::PARENT_KEY);
        return $explanation;
    }

    /**
     * Check if there is a permission set for the given key.
     *
     * @param Closure $permissionsGenerator
     * @param string $contentType
     * @param string $action
     * @param int $depth
     * @return PermissionsExplanation
     * @throws PermissionsException
     */
    protected function checkPermissionsRecursive(Closure $permissionsGenerator, string $contentType, string $action, int $depth = 0): PermissionsExplanation {
        // check we're not looping
        if($depth > self::MAX_INHERITANCE_DEPTH) {
            throw new PermissionsException('Max Depth Reached due to Inheritance Loop');
        }

        if($action === self::PARENT_KEY) {
            throw new PermissionsException('tried to access permissions with forbidden key: `' . self::PARENT_KEY . '`');
        }

        // if the key is set then we will return the value of it
        $exactMatch = $this->getPermissionsValue($permissionsGenerator, $contentType, $action);
        if($exactMatch !== null) {
            return $exactMatch;
        } else if($contentType === self::ROOT_CONTENT_TYPE) {
            return new PermissionsExplanation(false);
        } else {
            $parent = $this->explainParentContentType($permissionsGenerator, $contentType);

            // look in the parent contentType
            return $this->checkPermissionsRecursive($permissionsGenerator, $parent->getValue(), $action, $depth + 1);
        }
    }

}
