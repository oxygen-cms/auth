<?php

namespace Oxygen\Auth\Middleware;

use Closure;
use Illuminate\Auth\AuthManager;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Routing\UrlGenerator;
use Illuminate\Http\Request;
use Oxygen\Core\Contracts\Routing\ResponseFactory;
use Illuminate\Translation\Translator;
use Oxygen\Core\Http\Notification;

class Authenticate {

    /**
     * @var Guard
     */
    private $auth;

    /**
     * @var ResponseFactory
     */
    private $response;

    /**
     * @var Translator
     */
    private $lang;

    /**
     * @var UrlGenerator
     */
    private $url;

    /**
     * @param AuthManager $auth
     * @param ResponseFactory $response
     * @param UrlGenerator $generator
     * @param Translator $lang
     */
    public function __construct(AuthManager $auth, ResponseFactory $response,  UrlGenerator $generator, Translator $lang) {
        $this->auth = $auth;
        $this->response = $response;
        $this->lang = $lang;
        $this->url = $generator;
    }

    /**
     * Run the request filter.
     *
     * @param Request                       $request
     * @param Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next) {
        if($this->auth->guard()->guest()) {
            $request->session()->put('url.intended', $request->fullUrl());
            return $this->response->notification(
                new Notification($this->lang->get('oxygen/auth::messages.filter.notLoggedIn'), Notification::FAILED),
                ['redirect' => 'auth.getLogin']
            );
        }

        return $next($request);
    }

}