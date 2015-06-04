<?php

namespace Oxygen\Auth\Controller;

use Exception;

use Auth;
use Config;
use Hash;
use Event;
use Input;
use Oxygen\Auth\Repository\UserRepositoryInterface;
use Redirect;
use Response;
use URL;
use View;
use Lang;
use Validator;

use Oxygen\Crud\Controller\BasicCrudController;
use Oxygen\Core\Http\Notification;
use Oxygen\Core\Blueprint\Manager as BlueprintManager;

class AuthController extends BasicCrudController {

    /**
     * Constructs the AuthController.
     *
     * @param UserRepositoryInterface $repository
     * @param BlueprintManager        $manager
     */
    public function __construct(UserRepositoryInterface $repository, BlueprintManager $manager) {
        parent::__construct($repository, $manager, 'Auth');
    }

    /**
     * If logged in, redirect to the dashboard.
     * If not, show the login page.
     *
     * @return Response
     */
    public function getCheck() {
        if(Auth::check()) {
            return Redirect::intended(URL::route(Config::get('oxygen/auth::dashboard')));
        } else {
            return Redirect::guest(URL::route('auth.getLogin'));
        }
    }

    /**
     * Show the login form.
     *
     * @return Response
     */
    public function getLogin() {
        return View::make('oxygen/auth::login', [
            'title' => Lang::get('oxygen/auth::ui.login.title')
        ]);
    }

    /**
     * Login action.
     *
     * @return Response
     */
    public function postLogin() {
        $remember = Input::get('remember') === '1' ? true : false;

        if(Auth::attempt([
            'username' => Input::get('username'),
            'password' => Input::get('password')
        ], $remember)) {
            Event::fire('auth.login.successful', [Auth::user()]);

            return Response::notification(
                new Notification(
                    Lang::get('oxygen/auth::messages.login.successful', ['name' => Auth::user()->getFullName()])
                ),
                ['redirect' => Config::get('oxygen/auth::dashboard'), 'hardRedirect' => true]
            );
        } else {
            Event::fire('auth.login.failed', [Input::get('username')]);

            return Response::notification(
                new Notification(
                    Lang::get('oxygen/auth::messages.login.failed'),
                    Notification::FAILED
                )
            );
        }
    }

    /**
     * Log the user out.
     *
     * @return Response
     */
    public function postLogout() {
        $user = Auth::user();

        Auth::logout();

        Event::fire('auth.logout.successful', [$user]);

        return Response::notification(
            new Notification(Lang::get('oxygen/auth::messages.logout.successful')),
            ['redirect' => 'auth.getLogoutSuccess', 'hardRedirect' => true] // redirect without SmoothState
        );
    }

    /**
     * Show the logout success message.
     *
     * @return Response
     */
    public function getLogoutSuccess() {
        return View::make('oxygen/auth::logout', [
            'title' => Lang::get('oxygen/auth::ui.logout.title')
        ]);
    }

    /**
     * Show the current user's profile.
     *
     * @param mixed $foo useless param
     * @return Response
     */
    public function getInfo($foo = null) {
        $user = Auth::user();

        return View::make('oxygen/auth::profile', [
            'user' => $user,
            'title' => Lang::get('oxygen/auth::ui.profile.title')
        ]);
    }

    /**
     * Shows the update form.
     *
     * @param mixed $foo useless param
     * @return Response
     */
    public function getUpdate($foo = null) {
        $user = Auth::user();

        return View::make('oxygen/auth::update', [
            'user' => $user,
            'title' => Lang::get('oxygen/auth::ui.update.title')
        ]);
    }

    /**
     * Updates a the user.
     *
     * @param mixed $foo useless param
     * @return Response
     */
    public function putUpdate($foo = null) {
        $user = Auth::user();

        return parent::putUpdate($user);
    }

    /**
     * Redirects the user to the preferences.
     *
     * @return Response
     */
    public function getPreferences() {
        return Redirect::route('preferences.getView', ['user']);
    }

    /**
     * Change password form.
     *
     * @return Response
     */
    public function getChangePassword() {
        $user = Auth::user();

        return View::make('oxygen/auth::changePassword', [
            'user' => $user,
            'title' => Lang::get('oxygen/auth::ui.changePassword.title')
        ]);
    }

    /**
     * Change the user's password.
     *
     * @return Response
     */
    public function postChangePassword() {
        $user = Auth::user();
        $input = Input::all();

        $validator = Validator::make(
            $input,
            [
                'oldPassword' => ['required', 'hashes_to:' . $user->getPassword()],
                'password' => ['required', 'same:passwordConfirmation'],
                'passwordConfirmation' => ['required']
            ]
        );

        if($validator->passes()) {
            $user->setPassword($input['password']);
            $this->repository->persist($user);

            return Response::notification(
                new Notification(Lang::get('oxygen/auth::messages.password.changed')),
                ['redirect' => $this->blueprint->getRouteName('getInfo')]
            );
        } else {
            return Response::notification(
                new Notification($validator->messages()->first(), Notification::FAILED)
            );
        }
    }

    /**
     * Deletes the user permanently.
     *
     * @return Response
     */
    public function deleteForce() {
        $user = Auth::user();
        $this->repository->delete($user);

        return Response::notification(
            new Notification(Lang::get('oxygen/auth::messages.account.terminated')),
            ['redirect' => $this->blueprint->getRouteName('getLogin'), 'hardRedirect' => true]
        );
    }

}