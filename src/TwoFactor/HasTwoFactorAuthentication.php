<?php

namespace Oxygen\Auth\TwoFactor;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping as ORM;
use Oxygen\Auth\Entity\TwoFactorAuthentication;
use Oxygen\Auth\TwoFactor\Contracts\TwoFactorTotp;
use Oxygen\Auth\TwoFactor\Events\TwoFactorDisabled;
use Oxygen\Auth\TwoFactor\Events\TwoFactorEnabled;

/**
 * Implements {@see Contracts\TwoFactorAuthenticatable} for a Doctrine entity.
 *
 * Adapted from darkghosthunter/laraguard (MIT).
 */
trait HasTwoFactorAuthentication {

    /**
     * @ORM\OneToOne(targetEntity="Oxygen\Auth\Entity\TwoFactorAuthentication", mappedBy="authenticatable")
     * @var TwoFactorAuthentication|null
     */
    protected $twoFactorAuth;

    /**
     * Determines if the User has Two Factor Authentication enabled.
     */
    public function hasTwoFactorEnabled(): bool {
        return $this->twoFactorAuth != null && $this->twoFactorAuth->isEnabled();
    }

    /**
     * Enables Two Factor Authentication for the given user.
     */
    public function enableTwoFactorAuth(): void {
        $this->twoFactorAuth->setEnabledAt(now());

        app(EntityManager::class)->persist($this->twoFactorAuth);
        app(EntityManager::class)->flush();

        event(new TwoFactorEnabled($this));
    }

    /**
     * Disables Two Factor Authentication for the given user.
     */
    public function disableTwoFactorAuth(): void {
        $this->twoFactorAuth->flushAuth();
        app(EntityManager::class)->persist($this->twoFactorAuth);
        app(EntityManager::class)->flush();

        event(new TwoFactorDisabled($this));
    }

    /**
     * Creates a new Two Factor Auth mechanisms from scratch, and returns a new Shared Secret.
     */
    public function createTwoFactorAuth(): TwoFactorTotp {
        if ($this->twoFactorAuth == null) {
            $this->twoFactorAuth = new TwoFactorAuthentication($this);
        }
        $this->twoFactorAuth
            ->flushAuth()
            ->setLabel($this->twoFactorLabel());

        app(EntityManager::class)->persist($this->twoFactorAuth);
        app(EntityManager::class)->flush();

        return $this->twoFactorAuth;
    }

    /**
     * Returns the label for TOTP URI.
     */
    protected function twoFactorLabel(): string {
        return $this->email;
    }

    /**
     * Confirms the Shared Secret and fully enables the Two Factor Authentication.
     */
    public function confirmTwoFactorAuth(string $code): bool {
        if ($this->hasTwoFactorEnabled()) {
            return true;
        }

        if ($this->twoFactorAuth !== null && $this->twoFactorAuth->validateCode($code)) {
            $this->enableTwoFactorAuth();
            return true;
        }

        return false;
    }

    /**
     * Validates the TOTP Code.
     */
    public function validateTwoFactorCode(?string $code = null): bool {
        if (!$code || !$this->hasTwoFactorEnabled()) {
            return false;
        }

        return $this->twoFactorAuth->validateCode($code);
    }

    /**
     * Makes a Two Factor Code.
     */
    public function makeTwoFactorCode(): string {
        return $this->twoFactorAuth->makeTwoFactorCode();
    }
}
