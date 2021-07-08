<?php


namespace Oxygen\Auth\Permissions;


use Illuminate\Auth\AuthManager;
use Oxygen\Auth\Entity\User;

class Permissions {

    private PermissionsInterface $implementation;
    private AuthManager $auth;

    public function __construct(PermissionsInterface $implementation, AuthManager $auth) {
        $this->implementation = $implementation;
        $this->auth = $auth;
    }

    /**
     * Check if the user has permissions for the given key.
     *
     * @param User $user
     * @param string $key
     * @return boolean
     */
    public function hasForUser(User $user, string $key): bool {
        $generator = function() use($user) {
            $group = $user->getGroup();
            while($group !== null) {
                yield $group->getPermissions();
                $group = $group->getParent();
            }
        };

        return $this->implementation->hasPermissions($generator, $key);
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

}
