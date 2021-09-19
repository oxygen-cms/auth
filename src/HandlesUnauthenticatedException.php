<?php

namespace Oxygen\Auth;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Symfony\Component\HttpFoundation\Response;

trait HandlesUnauthenticatedException {

    /**
     * Convert an authentication exception into a response.
     * We customize this response to add 'code' => 'unauthenticated'
     *
     * @param Request  $request
     * @param AuthenticationException $exception
     * @return Response
     */
    protected function unauthenticated($request, AuthenticationException $exception) {
        // our SPA uses the `location` query parameter to decide where to send the user to next...
        $targetURL = Redirect::intended()->getTargetUrl();

        return $request->expectsJson()
            ? response()->json(['message' => $exception->getMessage(), 'code' => 'unauthenticated'], 401)
            : redirect()->guest($exception->redirectTo() ?? (route('login') . '?location=' . urlencode($targetURL)));
    }

}