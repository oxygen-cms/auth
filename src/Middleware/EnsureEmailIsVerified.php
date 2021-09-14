<?php

namespace Oxygen\Auth\Middleware;

use Closure;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;

class EnsureEmailIsVerified extends \Illuminate\Auth\Middleware\EnsureEmailIsVerified {

    /**
     * Handle an incoming request.
     *
     * @param Request  $request
     * @param Closure $next
     * @param string|null  $redirectToRoute
     * @return JsonResponse|RedirectResponse
     */
    public function handle($request, Closure $next, $redirectToRoute = null) {
        if (! $request->user() ||
            ($request->user() instanceof MustVerifyEmail &&
                ! $request->user()->hasVerifiedEmail())) {
            return $request->expectsJson()
                ? response()->json(['message' => 'Your email address is not verified.', 'code' => 'email_unverified'], 403)
                : Redirect::route($redirectToRoute ?: 'verification.notice');
        }

        return $next($request);
    }

}