<?php

namespace Oxygen\Auth\Controller;

use Illuminate\Auth\AuthManager;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Auth\StatefulGuard;
use Illuminate\Foundation\Auth\ThrottlesLogins;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\Factory;
use Illuminate\Validation\ValidationException;
use Lab404\Impersonate\Services\ImpersonateManager;
use Oxygen\Auth\Entity\User;
use Oxygen\Auth\Repository\UserRepositoryInterface;
use Oxygen\Data\Exception\InvalidEntityException;
use Oxygen\Preferences\PreferenceNotFoundException;
use Oxygen\Preferences\PreferencesManager;
use Illuminate\Routing\Controller;
use Oxygen\Core\Http\Notification;
use Illuminate\Http\Exceptions\HttpResponseException;
use Webmozart\Assert\Assert;

class AuthController extends Controller {

    use ThrottlesLogins;

    private UserRepositoryInterface $repository;
    private AuthManager $auth;

    /**
     * Constructs the AuthController.
     *
     * @param UserRepositoryInterface $repository
     * @param AuthManager $auth
     */
    public function __construct(UserRepositoryInterface $repository, AuthManager $auth) {
        $this->repository = $repository;
        $this->auth = $auth;
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
     * @return JsonResponse
     * @throws ValidationException
     */
    public function postLogin(Request $request) {
        if($this->auth->guard()->check()) {
            // if we're already logged in, then return right away
            return $this->makeAuthenticatedLoginResponse();
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

            $statefulGuard = $this->auth->guard();
            Assert::isInstanceOf($statefulGuard, StatefulGuard::class);

            if($statefulGuard->attempt($credentials)) {
                if($this->getUser()->isDeleted()) {
                    $statefulGuard->logout();
                    return response()->json([
                        'code' => 'account_deactivated'
                    ], 401);
                }
                return $this->makeAuthenticatedLoginResponse();
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
     * Returns preferences used to customize the login frontend view.
     *
     * @param PreferencesManager $preferencesManager
     * @return JsonResponse
     * @throws PreferenceNotFoundException
     */
    public function getLoginPreferences(PreferencesManager $preferencesManager) {
        return response()->json([
            'theme' => $preferencesManager->get('appearance.auth::theme')
        ]);
    }

    /**
     * @return JsonResponse
     */
    public function makeAuthenticatedLoginResponse(): JsonResponse {
        return response()->json([
            'user' => $this->getUser()->toArray(),
            'impersonating' => app(ImpersonateManager::class)->isImpersonating()
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
     * @return JsonResponse
     */
    public function postConfirmTwoFactor(Request $request) {
        $code = str_replace(' ', '', $request->input('2fa_code'));
        $activated = $request->user()->confirmTwoFactorAuth($code);

        if(!$activated) {
            return response()->json([
                'content' => __('oxygen/auth::messages.twoFactor.failure'),
                'status' => 'failed'
            ], 400);
        } else {
            return response()->json([
                'content' => __('oxygen/auth::messages.twoFactor.success'),
                'status' => 'success'
            ]);
        }
    }

    /**
     * Log the user out.
     *
     * @param Request $request
     * @return Response
     */
    public function postLogout(Request $request) {
        $statefulGuard = $this->auth->guard('web');
        Assert::isInstanceOf($statefulGuard, StatefulGuard::class);
        $statefulGuard->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->noContent();
    }

    /**
     * Change the user's password.
     *
     * @param Request $request
     * @param Factory $validationFactory
     * @return JsonResponse
     * @throws InvalidEntityException
     */
    public function postChangePassword(Request $request, Factory $validationFactory) {
        $user = $this->getUser();
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
                'content' => __('oxygen/auth::messages.password.changed'),
                'status' => Notification::SUCCESS
            ]);
        } else {
            return response()->json([
                'content' => $validator->messages()->first(),
                'status' => Notification::FAILED
            ]);
        }
    }

    private function getUser() {
        $user = $this->auth->guard()->user();
        Assert::isInstanceOf($user, User::class);
        return $user;
    }

}
