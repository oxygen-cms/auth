<?php

/**
 * From the DarkGhostHunter\Laraguard package...
 */

namespace Oxygen\Auth\Middleware;

use Closure;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Oxygen\Core\Http\Notification;
use DarkGhostHunter\Laraguard\Contracts\TwoFactorAuthenticatable;

class RequireTwoFactorEnabled {
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
    protected $response;

    /**
     * Create a new middleware instance.
     *
     * @param Authenticatable|null  $user
     * @param ResponseFactory $response
     */
    public function __construct(ResponseFactory $response, Authenticatable $user = null) {
        $this->response = $response;
        $this->user = $user;
    }

    /**
     * Handle an incoming request.
     *
     * @param  Request  $request
     * @param Closure $next
     * @param  string  $redirectToRoute
     * @return JsonResponse|RedirectResponse|mixed
     */
    public function handle($request, Closure $next, $redirectToRoute = '2fa.notice') {
        if(!$this->user->hasTwoFactorEnabled()) {
            return $this->response->json([
                'content' => trans('laraguard::messages.enable'),
                'code' => 'two_factor_setup_required',
                'status' => Notification::INFO
            ], 403);
        }

        return $next($request);
    }
}
