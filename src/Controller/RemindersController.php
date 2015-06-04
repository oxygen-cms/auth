<?php

namespace Oxygen\Auth\Controller;

use App;
use Blueprint;
use Input;
use Lang;
use Oxygen\Auth\Repository\UserRepositoryInterface;
use Oxygen\Core\Http\Notification;
use Password;
use Redirect;
use Response;
use URL;
use View;

use Oxygen\Core\Blueprint\Manager as BlueprintManager;
use Oxygen\Core\Controller\BlueprintController;

class RemindersController extends BlueprintController {

    /**
     * Constructs the controller.
     *
     * @param UserRepositoryInterface  $users
     * @param BlueprintManager         $manager
     */
    public function __construct(UserRepositoryInterface $users, BlueprintManager $manager) {
        parent::__construct($manager, 'Reminders');
        $this->users = $users;
    }

    /**
     * Display the password reminder view.
     *
     * @return Response
     */
    public function getRemind() {
        return View::make('oxygen/auth::reminders.remind', [
            'title' => Lang::get('oxygen/auth::ui.remind.title')
        ]);
    }

    /**
     * Handle a POST request to remind a user of their password.
     *
     * @return Response
     */
    public function postRemind() {
        $result = Password::remind(Input::only('email'), function($message) {
            $message->subject(Lang::get('oxygen/auth::messages.reminder.email.subject'));
        });

        $code = $result === Password::INVALID_USER ? 'failed' : 'success';

        return Response::notification(
            new Notification(Lang::get($result), $code)
        );
    }

    /**
     * Display the password reset view for the given token.
     *
     * @return Response
     */
    public function getReset() {
        if(!Input::has('token')) {
            App::abort(404);
        }

        return View::make('oxygen/auth::reminders.reset', [
            'token' => Input::get('token')
        ]);
    }

    /**
     * Handle a POST request to reset a user's password.
     *
     * @return Response
     */
    public function postReset() {
        $credentials = Input::only(
            'email', 'password', 'password_confirmation', 'token'
        );

        $response = Password::reset($credentials, function($user, $password) {
            $user->setPassword($password);
            $this->users->persist($user);
        });

        switch ($response) {
            case Password::INVALID_PASSWORD:
            case Password::INVALID_TOKEN:
            case Password::INVALID_USER:
                return Response::notification(
                    new Notification(Lang::get($response), 'failed')
                );
            case Password::PASSWORD_RESET:
                return Response::notification(
                    new Notification(Lang::get($response), 'success'),
                    ['redirect' => Blueprint::get('Auth')->getRouteName('getLogin'), 'hardRedirect' => true]
                );
        }
    }

}