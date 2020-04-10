<?php

namespace Oxygen\Auth\Middleware;

use Closure;
use Illuminate\Auth\AuthManager;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Config\Repository;
use Oxygen\Core\Contracts\Routing\ResponseFactory;
use Illuminate\Translation\Translator;
use Oxygen\Core\Http\Notification;
use Oxygen\Preferences\PreferencesManager;

class RedirectIfAuthenticated {

    /**
     * @var AuthManager
     */
    private $auth;

    /**
     * @var \Oxygen\Core\Contracts\Routing\ResponseFactory
     */
    private $response;

    /**
     * @var \Illuminate\Translation\Translator
     */
    private $lang;

    /**
     * @var \Oxygen\Preferences\PreferencesManager
     */
    private $preferences;

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
     * @return mixed
     * @throws \Oxygen\Preferences\PreferenceNotFoundException
     */
    public function handle($request, Closure $next) {
        if($this->auth->guard()->check()) {
            return $this->response->notification(
                new Notification($this->lang->get('oxygen/auth::messages.filter.alreadyLoggedIn')),
                ['redirect' => $this->preferences->get('modules.auth::dashboard')]
            );
        }

        return $next($request);
    }

}