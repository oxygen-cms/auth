<?php

namespace Oxygen\Auth\Controller;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\View\View;
use Oxygen\Core\Blueprint\BlueprintNotFoundException;
use Illuminate\Contracts\Auth\PasswordBroker;
use Illuminate\Contracts\Validation\Factory;
use Illuminate\Http\Request;
use Oxygen\Auth\Repository\UserRepositoryInterface;
use Oxygen\Core\Http\Notification;
use Oxygen\Core\Blueprint\BlueprintManager;
use Oxygen\Core\Controller\BlueprintController;
use ReflectionException;

class PasswordController extends Controller {
    /**
     * @var UserRepositoryInterface
     */
    private $users;

    /**
     * Constructs the controller.
     *
     * @param UserRepositoryInterface $users
     */
    public function __construct(UserRepositoryInterface $users) {
        $this->users = $users;
    }

    /**
     * Handle a POST request to remind a user of their password.
     *
     * @param PasswordBroker $password
     * @param Request $request
     * @return Response
     */
    public function postRemind(PasswordBroker $password, Request $request) {
        $result = $password->sendResetLink($request->only('email'));

        switch ($result) {
            case PasswordBroker::RESET_LINK_SENT:
                return notify(
                    new Notification(__($result), Notification::SUCCESS)
                );
            default:
                return notify(
                    new Notification(__($result), Notification::FAILED)
                );
        }
    }

    /**
     * Handle a POST request to reset a user's password.
     *
     * @param Request $request
     * @param PasswordBroker $password
     * @return JsonResponse
     */
    public function postReset(Request $request, PasswordBroker $password) {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|confirmed'
        ]);

        $credentials = $request->only(
            'email', 'password', 'password_confirmation', 'token'
        );

        $response = $password->reset($credentials, function($user, $password) {
            $user->setPassword($password);
            $this->users->persist($user);
        });

        switch ($response) {
            case PasswordBroker::PASSWORD_RESET:
                return response()->json([
                    'content' => __($response),
                    'status' => 'success'
                ]);
            default:
                return response()->json([
                    'code' => 'reset_failed',
                    'content' => __($response),
                    'status' => 'failed'
                ], 422);
        }
    }

}
