<?php

namespace Oxygen\Auth\Middleware;

use Closure;
use Illuminate\Auth\AuthManager;
use Illuminate\Http\Request;
use Oxygen\Core\Contracts\Routing\ResponseFactory;
use Oxygen\Core\Http\Notification;
use Oxygen\Core\Translation\Translator;
use Oxygen\Preferences\PreferenceNotFoundException;
use Oxygen\Preferences\PreferencesManager;
use Oxygen\Auth\Permissions\Permissions as PermissionsService;

class Permissions {

    /**
     * @var ResponseFactory
     */
    private $response;

    /**
     * @var Translator
     */
    private $lang;

    /**
     * @var PreferencesManager
     */
    private $preferences;

    private PermissionsService $permissions;

    /**
     * @param ResponseFactory $response
     * @param Translator $lang
     * @param PreferencesManager $preferences
     * @param PermissionsService $permissions
     */
    public function __construct(ResponseFactory $response, Translator $lang, PreferencesManager $preferences, PermissionsService $permissions) {
        $this->response = $response;
        $this->lang = $lang;
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
    public function handle(Request $request, Closure $next, string $permission) {
        if(!$this->permissions->has($permission)) {
            $notification = new Notification(
                $this->lang->get('oxygen/auth::messages.permissions.noPermissions', ['permission' => $permission]),
                Notification::FAILED
            );
            return $this->response->notification($notification, ['redirect' => $this->preferences->get('modules.auth::dashboard')]);
        }

        return $next($request);
    }

}
