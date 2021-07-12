<?php


namespace Oxygen\Auth\Middleware;

use Illuminate\Contracts\Session\Session;
use Illuminate\Session\Middleware\StartSession;
use Symfony\Component\HttpFoundation\Response;

class StartSessionIfNeeded extends StartSession {

    /**
     * Add the session cookie to the application response.
     *
     * @param Response $response
     * @param Session $session
     * @return void
     */
    protected function addCookieToResponse(Response $response, Session $session)
    {
        if (!app('auth')->guard()->check()) {
            return;
        }
        $this->addCookieToResponse($response, $session);
    }

}
