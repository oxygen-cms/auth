<?php

namespace Oxygen\Auth\Controller;

use DarkGhostHunter\Laraguard\Http\Controllers\Confirms2FACode;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Auth\AuthManager;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Events\Dispatcher;
use Illuminate\Foundation\Auth\ThrottlesLogins;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Session\SessionManager;
use Illuminate\Validation\Factory;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Oxygen\Auth\Entity\AuthenticationLogEntry;
use Oxygen\Auth\Entity\User;
use Oxygen\Auth\Repository\AuthenticationLogEntryRepositoryInterface;
use Oxygen\Auth\Repository\UserRepositoryInterface;
use Oxygen\Data\Exception\InvalidEntityException;
use Oxygen\Preferences\PreferenceNotFoundException;
use Oxygen\Preferences\PreferencesManager;
use Illuminate\Routing\Controller;
use Oxygen\Core\Http\Notification;
use Illuminate\Http\Exceptions\HttpResponseException;

class AuthController extends Controller {

    use Confirms2FACode;
    use ThrottlesLogins;

    private UserRepositoryInterface $repository;

    /**
     * Constructs the AuthController.
     *
     * @param UserRepositoryInterface $repository
     */
    public function __construct(UserRepositoryInterface $repository) {
        $this->repository = $repository;
    }

    /**
     * Returns the request parameter used for throttling login attempts.
     * In this case, we throttled based upon username.
     *
     * @return string
     */
    protected function username() {
        return 'username';
    }

    /**
     * Login action.
     *
     * @param Request $request
     * @param AuthManager $auth
     * @return JsonResponse
     * @throws ValidationException
     */
    public function postLogin(Request $request, AuthManager $auth) {
        if($auth->guard()->check()) {
            // if we're already logged in, then return right away
            return $this->makeAuthenticatedLoginResponse($auth->guard());
        }

        // we are just trying to see if we are already authenticated,
        // without providing any credentials
        $username = $request->get('username', null);
        if($username === '' || $username === null) {
            return response()->json([
                'code' => 'no_username'
            ], 401);
        }

        try {
            if($this->hasTooManyLoginAttempts($request)) {
                $this->fireLockoutEvent($request);
                $this->sendLockoutResponse($request);
            }

            $credentials = [
                'username' => $request->input('username'),
                'password' => $request->input('password')
            ];

            if ($auth->guard()->attempt($credentials)) {
                return $this->makeAuthenticatedLoginResponse($auth->guard());
            } else {
                $this->incrementLoginAttempts($request);

                return response()->json(
                    [
                        'code' => 'incorrect_username_password'
                    ],
                    401
                );
            }
        } catch(HttpResponseException $exception) {
            $this->incrementLoginAttempts($request);
            throw $exception;
        }
    }

    /**
     * @param Guard $guard
     * @return JsonResponse
     */
    public function makeAuthenticatedLoginResponse(Guard $guard) {
        return response()->json([
            'user' => $guard->user()->toArray()
        ]);
    }

    /**
     * Begins to set-up two-factor authentication for this user.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function postPrepareTwoFactor(Request $request) {
        $secret = $request->user()->createTwoFactorAuth();

        return response()->json([
            'as_qr_code' => $secret->toQr(),     // As QR Code
            'as_uri'     => $secret->toUri(),    // As "otpauth://" URI.
            'as_string'  => $secret->toString(), // As a string
        ]);
    }

    /**
     * Confirms the user has successfully setup two-factor authentication.
     *
     * @param Request $request
     * @param PreferencesManager $preferences
     * @return JsonResponse
     */
    public function postConfirmTwoFactor(Request $request, PreferencesManager $preferences) {
        $code = str_replace(' ', '', $request->input('2fa_code'));
        $activated = $request->user()->confirmTwoFactorAuth($code);

        if(!$activated) {
            return response()->json([
                'content' => __('oxygen/mod-auth::messages.twoFactor.failure'),
                'status' => 'failed'
            ], 400);
        } else {
            return response()->json([
                'content' => __('oxygen/mod-auth::messages.twoFactor.success'),
                'status' => 'success'
            ]);
        }
    }

    /**
     * Log the user out.
     *
     * @param AuthManager $auth
     * @param Request $request
     * @return mixed
     */
    public function postLogout(AuthManager $auth, Request $request) {
        $user = $auth->guard('web')->user();
        $auth->guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->noContent();
    }

    /**
     * Change the user's password.
     *
     * @param AuthManager $auth
     * @param Request $request
     * @param Factory $validationFactory
     * @return JsonResponse
     * @throws InvalidEntityException
     */
    public function postChangePassword(AuthManager $auth, Request $request, Factory $validationFactory) {
        $user = $auth->guard()->user();
        $input = $request->all();

        $validator = $validationFactory->make(
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

            return response()->json([
                'content' => __('oxygen/mod-auth::messages.password.changed'),
                'status' => Notification::SUCCESS
            ]);
        } else {
            return response()->json([
                'content' => $validator->messages()->first(),
                'status' => Notification::FAILED
            ]);
        }
    }

    /**
     * Change the user's password.
     *
     * @param AuthManager $auth
     * @param Request $request
     * @return JsonResponse
     * @throws InvalidEntityException
     */
    public function putUpdateFullName(AuthManager $auth, Request $request) {
        $user = $auth->guard()->user();
        $user->setFullName($request->get('fullName'));
        $this->repository->persist($user);

        return response()->json([
            'content' => __('oxygen/mod-auth::messages.fullNameChanged'),
            'status' => Notification::SUCCESS,
            'item' => $user->toArray()
        ]);
    }

    /**
     * Deletes the user permanently.
     *
     * @return JsonResponse
     */
    public function deleteForce(AuthManager $auth) {
        $user = $auth->guard()->user();
        $this->repository->delete($user);

        return response()->json([
            'content' => __('oxygen/mod-auth::messages.account.terminated'),
            'status' => Notification::SUCCESS
        ]);
    }

}
