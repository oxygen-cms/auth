<?php

namespace Oxygen\Auth\Controller;

use Exception;

use Auth;
use Config;
use Hash;
use Event;
use Input;
use Redirect;
use Response;
use URL;
use View;
use Lang;
use Validator;

use Oxygen\Crud\Controller\BasicCrudController;
use Oxygen\Core\Http\Notification;
use Oxygen\Core\Blueprint\Manager as BlueprintManager;
use Oxygen\Preferences\Transformer\InputTransformer;

class AuthController extends BasicCrudController {

    /**
     * Constructs the AuthController.
     *
     * @param BlueprintManager $manager
     */

    public function __construct(BlueprintManager $manager) {
        parent::__construct($manager, 'Auth', 'Oxygen\Auth\Model\User');
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
        return View::make('oxygen/auth::login');
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
                    Lang::get('oxygen/auth::messages.login.successful', ['name' => Auth::user()->full_name])
                ),
                ['redirect' => Config::get('oxygen/auth::dashboard')]
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
            ['redirect' => 'auth.getLogoutSuccess']
        );
    }

    /**
     * Show the logout success message.
     *
     * @return Response
     */

    public function getLogoutSuccess() {
        return View::make('oxygen/auth::logout');
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
            'user' => $user
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
            'user' => $user
        ]);
    }

    /**
     * Updates a Resource.
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
            'user' => $user
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
                'old_password' => ['required', 'hashes_to:' . $user->password],
                'password' => ['required', 'confirmed'],
                'password_confirmation' => ['required']
            ]
        );

        if($validator->passes()) {
            $user->password = $input['password'];
            $user->save();

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
        try {
            Auth::user()->forceDelete();

            return Response::notification(
                new Notification(Lang::get('oxygen/auth::messages.account.terminated')),
                ['redirect' => $this->blueprint->getRouteName('getLogin')]
            );
        } catch(Exception $e) {
            return Response::notification(
                new Notification(Lang::get('oxygen/crud::messages.account.terminateFailed'), Notification::FAILED)
            );
        }
    }

}