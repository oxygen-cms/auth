<?php

namespace Oxygen\Auth\Middleware;

use Closure;
use Illuminate\Auth\AuthManager;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Routing\UrlGenerator;
use Illuminate\Http\Request;
use Oxygen\Core\Contracts\CoreConfiguration;
use Oxygen\Core\Contracts\Routing\ResponseFactory;
use Illuminate\Translation\Translator;
use Oxygen\Core\Http\Notification;
use Illuminate\Support\Str;

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
     * @var CoreConfiguration
     */
    private $coreConfig;

    /**
     * @param AuthManager $auth
     * @param ResponseFactory $response
     * @param UrlGenerator $generator
     * @param Translator $lang
     */
    public function __construct(AuthManager $auth, ResponseFactory $response,  UrlGenerator $generator, Translator $lang, CoreConfiguration $coreConfig) {
        $this->auth = $auth;
        $this->response = $response;
        $this->lang = $lang;
        $this->url = $generator;
        $this->coreConfig = $coreConfig;
    }
    
    private function isPartOfApi($path) {
        return Str::startsWith($path, 'oxygen/api');
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
            $notification = new Notification($this->lang->get('oxygen/auth::messages.filter.notLoggedIn'), Notification::FAILED);
            if($this->isPartOfApi($request->path())) {
                $request->session()->put('adminMessage', $notification->toArray());
                return response()->json([
                    'status' => Notification::FAILED,
                    'authenticated' => false
                ]);
            } else {
                $request->session()->put('url.intended', $request->fullUrl());

                if(trim($request->path(), '/') === $this->coreConfig->getAdminUriPrefix()) {
                    // don't display an error message if we only visited '/oxygen'
                    return redirect()->route('auth.getLogin');
                }

                return $this->response->notification(
                    $notification,
                    ['redirect' => 'auth.getLogin', 'hardRedirect' => true]
                );
            }
        }

        return $next($request);
    }

}