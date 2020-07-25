<?php

/**
 * From the DarkGhostHunter\Laraguard package...
 */

namespace Oxygen\Auth\Middleware;

use Closure;
use Oxygen\Core\Http\Notification;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Routing\ResponseFactory;
use DarkGhostHunter\Laraguard\Contracts\TwoFactorAuthenticatable;
use Oxygen\Preferences\PreferencesManager;

class RequireTwoFactorDisabled {
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
    public function handle($request, Closure $next, $redirectToRoute = null) {
        if ($this->user->hasTwoFactorEnabled()) {
            if($redirectToRoute == null) {
                $redirectToRoute = app(PreferencesManager::class)->get('modules.auth::dashboard');
            }

            return $this->response->notification(
                new Notification(trans('laraguard::messages.already_enabled'), Notification::FAILED),
                ['redirect' => $redirectToRoute]
            );
        }

        return $next($request);
    }
}
