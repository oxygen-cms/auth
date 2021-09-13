<?php

namespace Oxygen\Auth\Controller;

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
//
//    /**
//     * Display the password reset view for the given token.
//     *
//     * @return View
//     */
//    public function reset(Request $request) {
//        if(!$request->has('token')) {
//            abort(404);
//        }
//
//        return view('oxygen/mod-auth::password.reset', [
//            'token' => $request->input('token')
//        ]);
//    }

    /**
     * Handle a POST request to reset a user's password.
     *
     * @param Request $request
     * @param PasswordBroker $password
     * @param Factory $validationFactory
     * @return Response
     * @throws BlueprintNotFoundException
     */
    public function postReset(Request $request, PasswordBroker $password, Factory $validationFactory) {
        $validator = $validationFactory->make(
            $request->all(),
            [
                'token' => 'required',
                'email' => 'required|email',
                'password' => 'required|confirmed'
            ]
        );

        if(!$validator->passes()) {
            return notify(
                new Notification($validator->messages()->first(), Notification::FAILED)
            );
        }

        $credentials = $request->only(
            'email', 'password', 'password_confirmation', 'token'
        );

        $response = $password->reset($credentials, function($user, $password) {
            $user->setPassword($password);
            $this->users->persist($user);
        });

        switch ($response) {
            case PasswordBroker::PASSWORD_RESET:
                return notify(
                    new Notification(__($response), Notification::SUCCESS),
                    ['redirect' => $this->blueprintManager->get('Auth')->getRouteName('getLogin'), 'hardRedirect' => true]
                );
            default:
                return notify(
                    new Notification(__($response), Notification::FAILED)
                );
        }
    }

}
