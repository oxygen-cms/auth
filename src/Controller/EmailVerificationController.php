<?php

namespace Oxygen\Auth\Controller;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Oxygen\Auth\Repository\UserRepositoryInterface;
use Oxygen\Data\Exception\InvalidEntityException;
use Symfony\Component\HttpFoundation\RedirectResponse;

class EmailVerificationController extends Controller {

    /**
     * Mark the authenticated user's email address as verified.
     *
     * @param Request $request
     * @param UserRepositoryInterface $users
     * @return RedirectResponse
     * @throws AuthorizationException
     * @throws InvalidEntityException
     */
    public function verify(Request $request, UserRepositoryInterface $users) {
        if(!hash_equals((string) $request->query('id'), (string) $request->user()->getKey())) {
            throw new AuthorizationException;
        }

        if(!hash_equals((string) $request->query('hash'), sha1($request->user()->getEmailForVerification()))) {
            throw new AuthorizationException;
        }

        if($request->user()->hasVerifiedEmail()) {
            return response()->redirectToRoute('dashboard.main');
        }

        if($request->user()->markEmailAsVerified()) {
            $users->persist($request->user());
            event(new Verified($request->user()));
        }

        return response()->redirectToRoute('dashboard.main');
    }

    /**
     * Send the email verification notification.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function sendNotification(Request $request) {
        if($request->user()->hasVerifiedEmail()) {
            return response()->json(['code' => 'already_verified'], 419);
        }

        $request->user()->sendEmailVerificationNotification();

        return response()->json(['code' => 'sent_email_verification']);
    }

}