<?php

// Two-factor (TOTP) authentication. Adapted from darkghosthunter/laraguard (MIT).
// Merged into config('two-factor'); override by publishing or in the application's config/two-factor.php.

return [

    /*
    | Name of the request input holding the TOTP code when logging in.
    */
    'input' => '2fa_code',

    /*
    | Codes can only be used once, so they are held in the cache for the period
    | in which they must not be reused. A `null` store uses the default cache store.
    */
    'cache' => [
        'store'  => null,
        'prefix' => '2fa.code',
    ],

    /*
    | The "2fa.confirm" middleware asks the user to re-confirm a code after this many seconds.
    */
    'confirm' => [
        'timeout' => 10800, // 3 hours
    ],

    /*
    | Length (in bytes) of the shared secret. 160 bits is recommended by RFC 4226.
    */
    'secret_length' => 20,

    /*
    | Issuer shown in authenticator apps. Defaults to the application name.
    */
    'issuer' => env('OTP_TOTP_ISSUER'),

    /*
    | TOTP parameters. These are stored per user, so changes only affect newly created secrets.
    */
    'totp' => [
        'digits'    => 6,
        'seconds'   => 30,
        'window'    => 1,
        'algorithm' => 'sha1',
    ],

    /*
    | SVG QR code size and margin.
    */
    'qr_code' => [
        'size'   => 200,
        'margin' => 4,
    ],
];
