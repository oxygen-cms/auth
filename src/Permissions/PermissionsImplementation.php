<?php

namespace Oxygen\Auth\Permissions;

use Closure;

interface PermissionsImplementation {

    /**
     * Gets the value for the provided permissions key.
     *
     * @param Closure   $permissionsGenerator a generator which returns permissions arrays to check, in reverse-order of inheritance
     * @param string    $key the permissions key in dot notation
     * @return PermissionsExplanation     if the permission is true or false, plus some expanatory data
     */
    function explainPermissions(Closure $permissionsGenerator, string $key): PermissionsExplanation;

    /**
     * @param Closure $permissionsGenerator
     * @param string $contentType
     * @return string|null
     */
    function explainParentContentType(Closure $permissionsGenerator, string $contentType): PermissionsExplanation;

}
