<?php

namespace Oxygen\Auth\Middleware;

use Closure;
use Illuminate\Auth\AuthManager;
use Oxygen\Auth\Entity\User;
use Oxygen\Core\Contracts\Routing\ResponseFactory;
use Oxygen\Core\Http\Notification;
use Oxygen\Core\Translation\Translator;
use Oxygen\Preferences\PreferencesManager;

class Permissions {

    /**
     * @var \Illuminate\Contracts\Auth\Guard
     */
    private $auth;

    /**
     * @var \Oxygen\Core\Contracts\Routing\ResponseFactory
     */
    private $response;

    /**
     * @var \Oxygen\Core\Translation\Translator
     */
    private $lang;

    /**
     * @var \Oxygen\Preferences\PreferencesManager
     */
    private $preferences;

    /**
     * @param AuthManager $auth
     * @param \Oxygen\Core\Contracts\Routing\ResponseFactory $response
     * @param \Oxygen\Core\Translation\Translator $lang
     * @param \Oxygen\Preferences\PreferencesManager $preferences
     */
    public function __construct(AuthManager $auth, ResponseFactory $response, Translator $lang, PreferencesManager $preferences) {
        $this->auth = $auth;
        $this->response = $response;
        $this->lang = $lang;
        $this->preferences = $preferences;
    }

    /**
     * Run the request filter.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @param string $permission
     * @return mixed
     * @throws \Oxygen\Preferences\PreferenceNotFoundException
     */
    public function handle($request, Closure $next, $permission) {
        $user = $this->auth->guard()->user();
        if(!($user instanceof User) || !$user->hasPermissions($permission)) {
            $notification = new Notification(
                $this->lang->get('oxygen/auth::messages.permissions.noPermissions', ['permission' => $permission]),
                Notification::FAILED
            );
            return $this->response->notification($notification, ['redirect' => $this->preferences->get('modules.auth::dashboard')]);
        }

        return $next($request);
    }

}
