<?php

/*
|--------------------------------------------------------------------------
| Message Language Lines
|--------------------------------------------------------------------------
|
| The following language lines are returned from API calls and inform the user
| if the action was successful or not.
|
*/

return [

    /*
    | ---------------
    | Auth
    | ---------------
    |
    | Authentication related messages.
    */

   'filter' => [
        'notLoggedIn'          => 'You need to be logged in to view that page.',
        'alreadyLoggedIn'      => 'You\'re already logged in'
    ],

    'login' => [
        'successful'           => 'Welcome, :name',
        'failed'               => 'Incorrect Username or Password'
    ],

    'logout' => [
        'successful'          => 'Logout Successful',
    ],

    /*
    | ---------------
    | Permissions
    | ---------------
    |
    | Messages related to the permissions system.
    */

    'permissions' => [
        'noPermissions' => 'Insufficient Permissions',
    ],

    /*
    | ---------------
    | Preferences
    | ---------------
    |
    | Messages related to the user's preferences.
    */

    'preferences' => [
        'updated'       => 'Preferences Updated',
        'updateFailed'  => 'Preferences Update Failed',
    ],

    /*
    | ---------------
    | Password
    | ---------------
    |
    | Messages related to changing the user's password.
    */

    'password' => [
        'invalid'       => 'The old password field is invalid',
        'changed'       => 'Password Changed',
        'changeFailed'  => 'Password Change Failed'
    ],

    /*
    | ---------------
    | Account
    | ---------------
    |
    | Messages relating to the destruction of the user's account.
    */

    'account' => [
        'terminated'      => 'Your account has been terminated',
        'terminateFailed' => 'Account Termination Failed'
    ]

];