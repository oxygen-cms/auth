<?php


namespace Oxygen\Auth\Permissions;

use Illuminate\Auth\AuthManager;
use Illuminate\Routing\Route;
use Illuminate\Routing\Router;
use Oxygen\Auth\Entity\Group;
use Oxygen\Auth\Entity\User;

class Permissions {

    private PermissionsInterface $implementation;
    private AuthManager $auth;
    private Router $router;
    private ?array $allPermissions = null;
    private ?array $allActions = null;
    private array $extraPermissions = [];

    public function __construct(PermissionsInterface $implementation, AuthManager $auth, Router $router) {
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
        return $this->explainForGroup($group, $key)->isPermitted();
    }

    /**
     * Check whether a group has permissions for the given key.
     * @param Group $group
     * @param string $key
     * @return PermissionsExplanation
     */
    public function explainForGroup(Group $group, string $key): PermissionsExplanation {
        $generator = function() use($group) {
            while($group !== null) {
                yield $group;
                $group = $group->getParent();
            }
        };

        return $this->implementation->explainPermissions($generator, $key);
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
                    return explode(':', $m)[1];
                }
            }
            return '';
        }, $this->router->getRoutes()->getRoutes());

        $permissionsFromRoutes = array_filter($permissionsStrings, function($row) { return $row !== ''; });

        $this->allPermissions = array_unique(array_merge($this->extraPermissions, $permissionsFromRoutes));

    }

}
