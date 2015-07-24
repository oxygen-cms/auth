<?php

namespace Oxygen\Auth\Middleware;

use Closure;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Routing\UrlGenerator;
use Illuminate\Session\Store;
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
     * @var \Illuminate\Session\Store
     */
    private $session;

    /**
     * @var \Illuminate\Contracts\Routing\UrlGenerator
     */
    private $url;

    /**
     * @param \Illuminate\Contracts\Auth\Guard               $auth
     * @param \Oxygen\Core\Contracts\Routing\ResponseFactory $response
     * @param \Illuminate\Session\Store                      $session
     * @param \Illuminate\Contracts\Routing\UrlGenerator     $generator
     * @param \Illuminate\Translation\Translator             $lang
     * @internal param $ \Illuminate\Session\Store
     */
    public function __construct(Guard $auth, ResponseFactory $response, Store $session, UrlGenerator $generator, Translator $lang) {
        $this->auth = $auth;
        $this->response = $response;
        $this->lang = $lang;
        $this->session = $session;
        $this->url = $generator;
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
            $route = 'auth.getLogin';
            
            $this->session->put('url.intended', $request->fullUrl());
            return $this->response->notification(
                new Notification($this->lang->get('oxygen/auth::messages.filter.notLoggedIn'), Notification::FAILED),
                ['redirect' => $route]
            );
        }

        return $next($request);
    }

}