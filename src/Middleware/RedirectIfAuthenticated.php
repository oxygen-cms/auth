<?php

namespace Oxygen\Auth\Middleware;

use Closure;
use Illuminate\Auth\AuthManager;
use Illuminate\Http\Request;
use Oxygen\Core\Contracts\Routing\ResponseFactory;
use Illuminate\Translation\Translator;
use Oxygen\Core\Http\Notification;
use Oxygen\Preferences\PreferenceNotFoundException;
use Oxygen\Preferences\PreferencesManager;

class RedirectIfAuthenticated {

    /**
     * @var AuthManager
     */
    private AuthManager $auth;

    /**
     * @var ResponseFactory
     */
    private ResponseFactory $response;

    /**
     * @var Translator
     */
    private Translator $lang;

    /**
     * @var PreferencesManager
     */
    private PreferencesManager $preferences;

    public function __construct(AuthManager $auth, ResponseFactory $response, Translator $lang, PreferencesManager $preferences) {
        $this->auth = $auth;
        $this->response = $response;
        $this->lang = $lang;
        $this->preferences = $preferences;
    }

    /**
     * Run the request filter.
     *
     * @param Request $request
     * @param Closure $next
     * @return mixed
     * @throws PreferenceNotFoundException
     */
    public function handle($request, Closure $next) {
        if($this->auth->guard()->check()) {
            if($request->wantsJson()) {
                return $this->response->json([
                    'content' => $this->lang->get('oxygen/auth::messages.filter.alreadyLoggedIn'),
                    'status' => Notification::WARNING
                ], 406);
            } else {
                return $this->response->notification(
                    new Notification($this->lang->get('oxygen/auth::messages.filter.alreadyLoggedIn'), Notification::WARNING),
                    ['redirect' => $this->preferences->get('modules.auth::dashboard')]
                );
            }
        }

        return $next($request);
    }

}
