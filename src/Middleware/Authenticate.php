<?php

namespace Oxygen\Auth\Middleware;

use Closure;
use Illuminate\Contracts\Auth\Guard;
use Oxygen\Core\Contracts\Routing\ResponseFactory;
use Illuminate\Translation\Translator;
use Oxygen\Core\Http\Notification;

class Authenticate {

    /**
     * @var \Illuminate\Contracts\Auth\Guard
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
     * @param \Illuminate\Contracts\Auth\Guard               $auth
     * @param \Oxygen\Core\Contracts\Routing\ResponseFactory $response
     * @param \Illuminate\Translation\Translator             $lang
     */
    public function __construct(Guard $auth, ResponseFactory $response, Translator $lang) {
        $this->auth = $auth;
        $this->response = $response;
        $this->lang = $lang;
    }

    /**
     * Run the request filter.
     *
     * @param \Illuminate\Http\Request                       $request
     * @param \Closure                                       $next
     * @return mixed
     */
    public function handle($request, Closure $next) {
        if($this->auth->guest()) {
            return $this->response->notification(
                new Notification($this->lang->get('oxygen/auth::messages.filter.notLoggedIn'), Notification::FAILED),
                ['redirect' => 'auth.getLogin']
            );
        }

        return $next($request);
    }

}