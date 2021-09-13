<?php

Route::middleware(['api'])->group(function () {

    Route::post('/oxygen/api/auth/login', '\Oxygen\Auth\Controller\AuthController@postLogin')
        ->name('auth.postLogin');

    // TODO: this should be redundant once all non-API routes are gone from Oxygen
    Route::get('/oxygen/auth/2fa-setup', [\App\Http\Controllers\AdminController::class, 'getView'])
        ->name('2fa.notice');

    Route::get('/oxygen/dashboard', [\App\Http\Controllers\AdminController::class, 'getView'])
        ->name('dashboard.main');

    Route::get('/oxygen/auth/reset-password', [\App\Http\Controllers\AdminController::class, 'getView'])
        ->name('password.reset');

    Route::post('/oxygen/api/auth/send-reminder-email', '\Oxygen\Auth\Controller\PasswordController@postRemind')
        ->name('password.postRemind')
        ->middleware(['oxygen.guest']);

//        ->makeAction([
//        'name' => 'postRemind',
//        'pattern' => 'remind',
//        'method' => 'POST',
//        'middleware' => ['web', 'oxygen.guest']
//    ], $factory);

    Route::post('/oxygen/api/auth/two-factor-setup', '\Oxygen\Auth\Controller\AuthController@postPrepareTwoFactor')
        ->name('auth.postPrepareTwoFactor')
        ->middleware(['oxygen.auth:sanctum', '2fa.disabled']);

    Route::post('/oxygen/api/auth/two-factor-confirm', '\Oxygen\Auth\Controller\AuthController@postConfirmTwoFactor')
        ->name('auth.postConfirmTwoFactor')
        ->middleware(['oxygen.auth:sanctum', '2fa.disabled']);

    Route::middleware(['oxygen.auth:sanctum', '2fa.require'])->group(function() {
        Route::post('/oxygen/api/auth/logout', '\Oxygen\Auth\Controller\AuthController@postLogout')
            ->name('auth.postLogout');

        Route::post('/oxygen/api/auth/login-log-entries', '\Oxygen\Auth\Controller\AuthController@getAuthenticationLogEntries')
            ->name('auth.getAuthenticationLogEntries')
            ->middleware('oxygen.permissions:auth.getAuthenticationLogEntries');

        Route::post('/oxygen/api/auth/ip-location/{ip}', '\Oxygen\Auth\Controller\AuthController@getIPGeolocation')
            ->name('auth.getIPGeolocation')
            ->middleware('oxygen.permissions:auth.getAuthenticationLogEntries');

        Route::put('/oxygen/api/auth/fullName', '\Oxygen\Auth\Controller\AuthController@putUpdateFullName')
            ->name('auth.putUpdateFullName')
            ->middleware('oxygen.permissions:auth.putUpdate');

        Route::post('/oxygen/api/auth/change-password', '\Oxygen\Auth\Controller\AuthController@postChangePassword')
            ->name('auth.postChangePassword')
            ->middleware(['oxygen.permissions:auth.postChangePassword']);

        Route::post('/oxygen/api/auth/terminate-account', '\Oxygen\Auth\Controller\AuthController@deleteForce')
            ->name('auth.deleteForce')
            ->middleware(['oxygen.permissions:auth.deleteForce']);
    });

});


