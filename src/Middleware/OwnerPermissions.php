<?php

namespace Oxygen\Auth\Middleware;

use Illuminate\Http\Request;
use Oxygen\Auth\Permissions\Permissions as PermissionsService;
use Oxygen\Core\Contracts\Routing\ResponseFactory;
use Oxygen\Core\Http\Notification;
use Oxygen\Core\Translation\Translator;
use Oxygen\Preferences\PreferenceNotFoundException;
use Oxygen\Preferences\PreferencesManager;
use Oxygen\Auth\Permissions\OwnedByUser;

class OwnerPermissions {

    /**
     * @var PreferencesManager
     */
    private $preferences;

    private PermissionsService $permissions;

    /**
     * @param PreferencesManager $preferences
     * @param PermissionsService $permissions
     */
    public function __construct(PreferencesManager $preferences, PermissionsService $permissions) {
        $this->preferences = $preferences;
        $this->permissions = $permissions;
    }

    /**
     * Run the request filter.
     *
     * @param Request $request
     * @param Closure $next
     * @param string $permission
     * @return mixed
     * @throws PreferenceNotFoundException
     */
    public function handle(Request $request, \Closure $next, string $permission, string $permissionIfOwned, ...$models) {
        $owned = true;
        foreach($request->route()->parameters() as $parameter) {
            if(!($parameter instanceof OwnedByUser) || $parameter->getOwner() != $request->user()) {
                $owned = false;
            }
        }

        if(!$this->permissions->has($owned ? $permissionIfOwned : $permission)) {
            return response()->json([
                'content' => trans('oxygen/auth::messages.permissions.noPermissions', ['permission' => $permission]),
                'status' => 'failed'
            ]);
        }

        return $next($request);
    }

}