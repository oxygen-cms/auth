<?php

namespace Oxygen\Auth\TwoFactor\Events;

use Oxygen\Auth\TwoFactor\Contracts\TwoFactorAuthenticatable;

class TwoFactorDisabled {
    /**
     * The User using Two Factor Authentication.
     *
     * @var \Oxygen\Auth\TwoFactor\Contracts\TwoFactorAuthenticatable
     */
    public $user;

    /**
     * Create a new event instance.
     *
     * @param  \Oxygen\Auth\TwoFactor\Contracts\TwoFactorAuthenticatable  $user
     * @return void
     */
    public function __construct(TwoFactorAuthenticatable $user) {
        $this->user = $user;
    }
}
