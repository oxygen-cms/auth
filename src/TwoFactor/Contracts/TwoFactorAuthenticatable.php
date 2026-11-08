<?php

namespace Oxygen\Auth\TwoFactor\Contracts;

interface TwoFactorAuthenticatable {
    /**
     * Determines if the User has Two Factor Authentication enabled or not.
     *
     * @return bool
     */
    public function hasTwoFactorEnabled() : bool;

    /**
     * Enables Two Factor Authentication for the given user.
     *
     * @return void
     */
    public function enableTwoFactorAuth() : void;

    /**
     * Disables Two Factor Authentication for the given user.
     *
     * @return void
     */
    public function disableTwoFactorAuth() : void;

    /**
     * Recreates the Two Factor Authentication from the ground up, and returns a new Shared Secret.
     *
     * @return \Oxygen\Auth\TwoFactor\Contracts\TwoFactorTotp
     */
    public function createTwoFactorAuth() : TwoFactorTotp;

    /**
     * Confirms the Shared Secret and fully enables the Two Factor Authentication.
     *
     * @param  string  $code
     * @return bool
     */
    public function confirmTwoFactorAuth(string $code) : bool;

    /**
     * Validates the TOTP Code.
     *
     * @param  string  $code
     * @return bool
     */
    public function validateTwoFactorCode(?string $code = null) : bool;

    /**
     * Makes a Two Factor Code/
     * @return string
     */
    public function makeTwoFactorCode() : string;
}
