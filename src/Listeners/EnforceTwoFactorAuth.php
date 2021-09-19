<?php

namespace Oxygen\Auth\Listeners;

use DarkGhostHunter\Laraguard\Rules\TotpCodeRule;
use Illuminate\Auth\Events\Validated;
use Illuminate\Http\Exceptions\HttpResponseException;

class EnforceTwoFactorAuth {
    public function handle(Validated $event) {
        if(!$event->user->hasTwoFactorEnabled()) {
            return;
        }

        $request = app('request');
        $code = $request->input(config('laraguard.input'));
        $validator = validator([
            'code' => $code
        ], [
            'code' => ['required', new TotpCodeRule($event->user)]
        ]);

        if($validator->fails()) {
            throw new HttpResponseException(response()->json([
                'code' => 'two_factor_auth_failed'
            ], 401));
        }

        $request->session()->put('2fa.totp_confirmed_at', now()->timestamp);
    }
}