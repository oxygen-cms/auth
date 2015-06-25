<?php

namespace Oxygen\Auth\Middleware;

use Closure;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Config\Repository;
use Oxygen\Core\Contracts\Routing\ResponseFactory;
use Illuminate\Translation\Translator;
use Oxygen\Core\Http\Notification;
use Oxygen\Preferences\PreferencesManager;

class RedirectIfAuthenticated {

    /**
     * Run the request filter.
     *
     * @param  \Illuminate\Http\Request                       $request
     * @param  \Closure                                       $next
     * @param  \Illuminate\Contracts\Auth\Guard               $auth
     * @param  \Oxygen\Core\Contracts\Routing\ResponseFactory $response
     * @param  \Illuminate\Translation\Translator             $lang
     * @param  \Oxygen\Preferences\PreferencesManager         $preferences
     * @return mixed
     */
    public function handle($request, Closure $next, Guard $auth, ResponseFactory $response, Translator $lang, PreferencesManager $preferences) {
        if($auth->check()) {
            return $response->notification(
                new Notification($lang->get('oxygen/auth::messages.filter.alreadyLoggedIn')),
                ['redirect' => $preferences->get('modules.auth::dashboard')]
            );
        }

        return $next($request);
    }

}