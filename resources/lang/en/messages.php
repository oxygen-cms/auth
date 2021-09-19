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

   'filter' => [
        'alreadyLoggedIn'      => 'You\'re already logged in'
    ],

    'permissions' => [
        'noPermissions' => 'Insufficient Permissions',
    ],


    'newDeviceNotification' => [
        'subject' => 'Login from a new IP address/browser',
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
        'terminated'      => 'The account has been terminated',
        'terminateFailed' => 'Account Termination Failed'
    ],

    /*
    | ---------------
    | Password Reminders
    | ---------------
    |
    | Messages relating to password reminders.
    */

    'reminder' => [
        'email'      => [
            'subject'    => 'Password Reminder'
        ]
    ],

    'impersonated' => 'Now impersonating :name',
    'impersonationStopped' => 'Welcome back - :name!',
    'cannotImpersonateSameUser' => 'Cannot impersonate oneself',
    'notImpersonating' => 'Never started impersonating in the first place',

    'twoFactor' => [
        'success' => 'Code accepted',
        'failure' => 'Sorry, but that\'s not a valid code. Try again'
    ],

    'fullNameChanged' => 'Full Name updated',

    'accountCreated' => 'An account has been created and an invite link has been sent to :email with further instructions'

];