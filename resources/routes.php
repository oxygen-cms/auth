<?php

use Illuminate\Routing\Router;
use Oxygen\Auth\Controller\AuthController;
use Oxygen\Auth\Controller\AuthenticationLogController;
use Oxygen\Auth\Controller\EmailVerificationController;
use Oxygen\Auth\Controller\PasswordController;
use Oxygen\Auth\Controller\UsersController;
use Oxygen\Auth\Controller\GroupsController;

Route::prefix('/oxygen/api')->middleware(['api'])->group(function(Router $router) {

    $router->post('auth/login',  [AuthController::class, 'postLogin'])
        ->name('auth.postLogin');

    $router->get('auth/preferences',  [AuthController::class, 'getLoginPreferences'])
        ->name('auth.getLoginPreferences');

    $router->post('auth/send-reminder-email', [PasswordController::class, 'postRemind'])
        ->name('password.postRemind')
        ->middleware(['oxygen.guest']);

    $router->post('auth/reset-password', [PasswordController::class, 'postReset'])
        ->name('password.postReset')
        ->middleware(['oxygen.guest']);

    $router->post('auth/two-factor-setup', [AuthController::class, 'postPrepareTwoFactor'])
        ->name('auth.postPrepareTwoFactor')
        ->middleware(['auth:sanctum', '2fa.disabled']);

    $router->post('auth/two-factor-confirm', [AuthController::class, 'postConfirmTwoFactor'])
        ->name('auth.postConfirmTwoFactor')
        ->middleware(['auth:sanctum', '2fa.disabled']);

    $router->post('auth/verify-email', [EmailVerificationController::class, 'sendNotification'])
        ->name('auth.sendVerifyEmail')
        ->middleware(['auth:sanctum', '2fa.require', 'throttle:6,1']);
});

Route::get('/oxygen/verify-email', [EmailVerificationController::class, 'verify'])
    ->name('verification.verify')
    ->middleware(['web', 'auth', 'signed']);

Route::prefix('/oxygen/api/auth')->middleware('api_auth')->group(function(Router $router) {
    $router->post('logout', [AuthController::class, 'postLogout'])
        ->name('auth.postLogout');

    $router->post('login-log-entries', [AuthenticationLogController::class, 'getAuthenticationLogEntries'])
        ->name('auth.getAuthenticationLogEntries')
        ->middleware('oxygen.permissions:auth.getAuthenticationLogEntries');

    $router->get('sessions', [AuthenticationLogController::class, 'getUserSessions'])
        ->name('auth.getUserSessions')
        ->middleware('oxygen.permissions:auth.getUserSessions');

    $router->delete('sessions/{sessionId}', [AuthenticationLogController::class, 'deleteUserSession'])
        ->name('auth.deleteUserSession')
        ->middleware('oxygen.permissions:auth.getUserSessions');

    $router->post('ip-location/{ip}', [AuthenticationLogController::class, 'getIPGeolocation'])
        ->name('auth.getIPGeolocation')
        ->middleware('oxygen.permissions:auth.getAuthenticationLogEntries');

    $router->post('change-password', [AuthController::class, 'postChangePassword'])
        ->name('auth.postChangePassword')
        ->middleware(['oxygen.permissions:auth.postChangePassword']);

    $router->post('terminate-account', [AuthController::class, 'deleteForce'])
        ->name('auth.deleteForce')
        ->middleware(['oxygen.permissions:auth.deleteForce']);
});

Route::prefix('/oxygen/api/users')->middleware('api_auth')->group(function(Router $router) {
    UsersController::registerCrudRoutes($router);
    UsersController::registerSoftDeleteRoutes($router);

    $router->get('{user}/basic', [UsersController::class, 'getInfoApiBasic'])
        ->name('users.getInfoBasic')
        ->middleware('oxygen.permissions:users.getInfoBasic');

    $router->put('{user}/fullName', [UsersController::class, 'putUpdateFullName'])
        ->name('users.putUpdateFullName')
        ->middleware('oxygen.ownerPermissions:users.putUpdate,users.owner_putUpdateFullName');

    $router->delete('/{id}/force', [UsersController::class, 'deleteForce'])
        ->name("users.deleteForce")
        ->middleware("oxygen.ownerPermissions:users.deleteForce,users.owner_deleteForce");

    $router->post('{id}/impersonate', [UsersController::class, 'postImpersonate'])
        ->name('users.postImpersonate')
        ->middleware('oxygen.permissions:users.postImpersonate');
});

Route::prefix('/oxygen/api/users')->middleware('api_auth_unverified')->group(function(Router $router) {
    $router->post('stop-impersonating', [UsersController::class, 'postLeaveImpersonate'])
        ->name('users.postLeaveImpersonate');
});

Route::prefix('/oxygen/api/groups')->middleware('api_auth')->group(function(Router $router) {
    GroupsController::registerCrudRoutes($router);
    GroupsController::registerSoftDeleteRoutes($router);
});


