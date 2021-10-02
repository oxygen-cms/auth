<?php

namespace Oxygen\Auth\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Oxygen\Auth\Permissions\Permissions as PermissionsService;
use Oxygen\Preferences\PreferencesManager;
use Oxygen\Auth\Permissions\OwnedByUser;
use Webmozart\Assert\Assert;

class OwnerPermissions {

    /**
     * @var PreferencesManager
     */
    private PreferencesManager $preferences;

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
     * @param string $permissionIfOwned
     * @param mixed ...$models
     * @return mixed
     */
    public function handle(Request $request, Closure $next, string $permission, string $permissionIfOwned, ...$models) {
        $owned = true;
        $route = $request->route();
        Assert::isInstanceOf($route, Route::class);
        foreach($route->parameters() as $parameter) {
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