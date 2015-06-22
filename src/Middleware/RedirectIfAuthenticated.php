<?php

namespace Oxygen\Auth\Middleware;
    
use Closure;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Config\Repository;
use Oxygen\Core\Contracts\Routing\ResponseFactory;
use Illuminate\Translation\Translator;
use Oxygen\Core\Http\Notification;

class RedirectIfAuthenticated {

    /**
     * Authentication dependency.
     *
     * @var Guard
     */
    protected $auth;

    /**
     * Response factory
     *
     * @var ResponseFactory
     */
    protected $response;

    /**
     * Translator dependency
     *
     * @var Translator
     */
    protected $lang;

    /**
     * Config repository
     *
     * @var Repository
     */
    protected $config;

    /**
     * Constructs the Authentication middleware
     *
     * @param Guard             $auth       AuthManager instance
     * @param ResponseFactory   $response   Response facade
     * @param Translator        $lang       Translator instance
     */
    public function __construct(Guard $auth, ResponseFactory $response, Translator $lang, Repository $config) {
        $this->auth     = $auth;
        $this->response = $response;
        $this->lang     = $lang;
        $this->config   = $config;
    }

    /**
     * Run the request filter.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next) {
        if($this->auth->check()) {
            return $this->response->notification(
                new Notification($this->lang->get('oxygen/auth::messages.filter.alreadyLoggedIn')),
                ['redirect' => $this->config->get('oxygen/auth::dashboard')]
            );
        }

        return $next($request);
    }

}