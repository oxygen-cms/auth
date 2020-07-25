<?php

/**
 * From the DarkGhostHunter\Laraguard package...
 */

namespace Oxygen\Auth\Middleware;

use Closure;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Routing\ResponseFactory;
use Oxygen\Core\Http\Notification;
use DarkGhostHunter\Laraguard\Contracts\TwoFactorAuthenticatable;

class RequireTwoFactorEnabled {
    /**
     * Current User authenticated.
     *
     * @var \Illuminate\Contracts\Auth\Authenticatable|\DarkGhostHunter\Laraguard\Contracts\TwoFactorAuthenticatable|null
     */
    protected $user;

    /**
     * Response Factory.
     *
     * @var \Illuminate\Contracts\Routing\ResponseFactory
     */
    protected $response;

    /**
     * Create a new middleware instance.
     *
     * @param  \Illuminate\Contracts\Auth\Authenticatable|null  $user
     * @param  \Illuminate\Contracts\Routing\ResponseFactory  $response
     */
    public function __construct(ResponseFactory $response, Authenticatable $user = null) {
        $this->response = $response;
        $this->user = $user;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  $redirectToRoute
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse|mixed
     */
    public function handle($request, Closure $next, $redirectToRoute = '2fa.notice') {
        if (!$this->user->hasTwoFactorEnabled()) {
            return $this->response->notification(
                new Notification(trans('laraguard::messages.enable'), Notification::INFO),
                ['redirect' => $redirectToRoute]
            );
        }

        return $next($request);
    }
}
