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
    |--------------------------------------------------------------------------
    | Authentication Log Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines are used during authentication log for
    | various messages that we need to display to the user. You are free to
    | modify these language lines according to your application's requirements.
    |
    */

    'newDeviceNotification' => [
        'subject' => 'Login from a new IP address/browser',
    ]

];