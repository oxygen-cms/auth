<?php

namespace Oxygen\Auth\Middleware;

use Closure;
use Illuminate\Contracts\Auth\Guard;
use Oxygen\Core\Contracts\Routing\ResponseFactory;
use Oxygen\Core\Http\Notification;
use Oxygen\Core\Translation\Translator;
use Oxygen\Preferences\PreferencesManager;

class Permissions {

    /**
     * Run the request filter.
     *
     * @param \Illuminate\Http\Request                       $request
     * @param \Closure                                       $next
     * @param \Illuminate\Contracts\Auth\Guard               $auth
     * @param \Oxygen\Core\Contracts\Routing\ResponseFactory $response
     * @param \Oxygen\Core\Translation\Translator            $lang
     * @param \Oxygen\Preferences\PreferencesManager         $preferences
     * @param                                                $permission
     * @return mixed
     */
    public function handle($request, Closure $next, Guard $auth, ResponseFactory $response, Translator $lang, PreferencesManager $preferences, $permission) {
        if(!$auth->user()->hasPermissions($permission)) {
            $notification = new Notification(
                $lang->get('oxygen/auth::messages.permissions.noPermissions', ['permission' => $permission]),
                Notification::FAILED
            );
            return $response->notification($notification, ['redirect' => $preferences->get('modules.auth::dashboard')]);
        }

        return $next($request);
    }

}