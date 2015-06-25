<?php

namespace Oxygen\Auth\Middleware;

use Closure;
use Illuminate\Contracts\Auth\Guard;
use Oxygen\Core\Contracts\Routing\ResponseFactory;
use Illuminate\Translation\Translator;
use Oxygen\Core\Http\Notification;

class Authenticate {

    /**
     * Run the request filter.
     *
     * @param \Illuminate\Http\Request                       $request
     * @param \Closure                                       $next
     * @param \Illuminate\Contracts\Auth\Guard               $auth
     * @param \Oxygen\Core\Contracts\Routing\ResponseFactory $response
     * @param \Illuminate\Translation\Translator             $lang
     * @return mixed
     */
    public function handle($request, Closure $next, Guard $auth, ResponseFactory $response, Translator $lang) {
        if($auth->guest()) {
            return $response->notification(
                new Notification($lang->get('oxygen/auth::messages.filter.notLoggedIn'), Notification::FAILED),
                ['redirect' => 'auth.getLogin']
            );
        }

        return $next($request);
    }

}