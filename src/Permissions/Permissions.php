<?php


namespace Oxygen\Auth\Permissions;

use Illuminate\Auth\AuthManager;
use Illuminate\Routing\Route;
use Illuminate\Routing\Router;
use Oxygen\Auth\Entity\Group;
use Oxygen\Auth\Entity\User;
use Oxygen\Core\Permissions\PermissionsInterface;

class Permissions implements PermissionsInterface {

    private PermissionsImplementation $implementation;
    private AuthManager $auth;
    private Router $router;
    private ?array $allPermissions = null;
    private ?array $allActions = null;
    private array $extraPermissions = [];

    public function __construct(PermissionsImplementation $implementation, AuthManager $auth, Router $router) {
        $this->implementation = $implementation;
        $this->auth = $auth;
        $this->router = $router;
    }

    /**
     * Check if the user has permissions for the given key.
     *
     * @param User $user
     * @param string $key
     * @return boolean
     */
    public function hasForUser(User $user, string $key): bool {
        $group = $user->getGroup();
        return $this->hasForGroup($group, $key);
    }

    /**
     * Check whether a group has permissions for the given key.
     * @param Group $group
     * @param string $key
     * @return bool
     */
    public function hasForGroup(Group $group, string $key): bool {
        return (bool) $this->explainForGroup($group, $key)->getValue();
    }

    /**
     * Check whether a group has permissions for the given key.
     * @param Group $group
     * @param string $key
     * @return PermissionsExplanation
     */
    public function explainForGroup(Group $group, string $key): PermissionsExplanation {
        $generator = $this->getGroupsGenerator($group);
        return $this->implementation->explainPermissions($generator, $key);
    }

    /**
     * Determines a parent content type for a given content type.
     * @param Group $group
     * @param string $contentType
     * @return PermissionsExplanation
     */
    public function explainParentForGroup(Group $group, string $contentType): PermissionsExplanation {
        $generator = $this->getGroupsGenerator($group);
        return $this->implementation->explainParentContentType($generator, $contentType);
    }

    private function getGroupsGenerator(Group $group) {
        return function() use($group) {
            while($group !== null) {
                yield $group;
                $group = $group->getParent();
            }
        };
    }

    /**
     * Check if the user has permissions for the given key.
     *
     * @param string $key
     * @return boolean
     */
    public function has(string $key): bool {
        return $this->hasForUser($this->auth->guard()->user(), $key);
    }

    /**
     * Registers a permission with key $key as recognised by the application.
     * Permissions used by the `oxygen.permissions` route-middleware are automatically recognised.
     *
     * @param $key
     */
    public function registerPermission($key) {
        $this->extraPermissions[] = $key;
    }

    /**
     * @return array
     */
    public function getAllPermissions(): array {
        if($this->allPermissions === null) {
            $this->gatherUsedPermissionsStrings();
        }
        return $this->allPermissions;
    }

    /**
     * Returns a list of content types referenced by the application.
     *
     * @return array
     */
    public function getAllContentTypes(): array {
        $permissions = $this->getAllPermissions();
        $contentTypes = [];
        foreach($permissions as $permission) {
            list($contentType, $action) = explode('.', $permission);
            $contentTypes[] = $contentType;
        }
        return array_unique($contentTypes);
    }

    /**
     * Returns an array whose keys are all the possible actions, regardless of content type.
     * @return array|null
     */
    public function getAllActions(): array {
        if($this->allActions === null) {
            $this->gatherAllPossibleActions($this->getAllPermissions());
        }
        return $this->allActions;
    }

    private function gatherAllPossibleActions(array $permissionStrings) {
        $actions = [];
        foreach($permissionStrings as $s) {
            list($contentType, $action) = explode('.', $s);
            $actions[$action] = true;
        }
        $this->allActions = $actions;
    }

    /**
     * @return void
     */
    private function gatherUsedPermissionsStrings() {
        $permissionsStrings = array_map(function(Route $route) {
            $middleware = $route->gatherMiddleware();
            foreach($middleware as $m) {
                if(str_starts_with($m, 'oxygen.permissions:')) {
                    return [explode(':', $m)[1]];
                } else if(str_starts_with($m, 'oxygen.ownerPermissions:')) {
                    return explode(',', explode(':', $m)[1]);
                }
            }
            return [];
        }, $this->router->getRoutes()->getRoutes());

        $permissionsFromRoutes = array_merge(...$permissionsStrings);

        $this->allPermissions = array_unique(array_merge($this->extraPermissions, $permissionsFromRoutes));

    }

}
