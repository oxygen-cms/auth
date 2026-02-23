<?php

/**
 * From the DarkGhostHunter\Laraguard package...
 */

namespace Oxygen\Auth\Middleware;

use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Oxygen\Core\Http\Notification;
use Illuminate\Contracts\Auth\Authenticatable;
use Oxygen\Core\Contracts\Routing\ResponseFactory;
use DarkGhostHunter\Laraguard\Contracts\TwoFactorAuthenticatable;
use Oxygen\Core\Preferences\PreferencesManager;

class RequireTwoFactorDisabled {
    /**
     * Current User authenticated.
     *
     * @var Authenticatable|TwoFactorAuthenticatable|null
     */
    protected $user;

    /**
     * Response Factory.
     *
     * @var ResponseFactory
     */
    protected ResponseFactory $response;

    /**
     * Create a new middleware instance.
     *
     * @param Authenticatable|null $user
     * @param ResponseFactory $response
     */
    public function __construct(ResponseFactory $response, Authenticatable $user = null) {
        $this->response = $response;
        $this->user = $user;
    }

    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     * @param string $redirectToRoute
     * @return JsonResponse|RedirectResponse|mixed
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
