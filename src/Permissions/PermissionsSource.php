<?php

namespace Oxygen\Auth\Permissions;

interface PermissionsSource {

    /**
     * Returns the group's permissions.
     *
     * @return array
     */
    public function getPermissions(): array;

    public function getName();

    public function getFlatPermissions(): array;

    public function getNickname();

    /**
     * Explicitly denies the specified permission for the group.
     *
     * @param string $key
     */
    public function denyPermissions(string $key);

    /**
     * Grants the specified permissions to the group.
     *
     * @param string $key
     */
    public function grantPermissions(string $key);

    /**
     * @param string $contentType
     * @param string|null $parentContentType
     */
    public function setPermissionInheritance(string $contentType, ?string $parentContentType);

    /**
     * Unsets denies the specified permission for the group, reverting it to a default value.
     *
     * @param string $key
     */
    public function unsetPermissions(string $key);

}