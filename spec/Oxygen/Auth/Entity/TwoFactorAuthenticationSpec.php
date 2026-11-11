<?php

namespace spec\Oxygen\Auth\Entity;

use Illuminate\Cache\CacheManager;
use Illuminate\Config\Repository;
use Illuminate\Container\Container;
use Oxygen\Auth\Entity\TwoFactorAuthentication;
use PhpSpec\ObjectBehavior;

class TwoFactorAuthenticationSpec extends ObjectBehavior {

    function let() {
        // minimal Laravel container so the config() and cache() helpers work
        $app = new Container();
        Container::setInstance($app);
        $app->instance('config', new Repository([
            'two-factor' => require __DIR__ . '/../../../../config/two-factor.php',
            'cache' => [
                'default' => 'array',
                'stores' => ['array' => ['driver' => 'array']],
            ],
        ]));
        $app->instance('cache', new CacheManager($app));

        $this->beConstructedWith(null);
        $this->flushAuth();
    }

    function it_is_initially_disabled() {
        $this->isEnabled()->shouldReturn(false);
        $this->isDisabled()->shouldReturn(true);
    }

    function it_generates_a_base32_secret_of_the_configured_length() {
        $secret = TwoFactorAuthentication::generateRandomSecret();

        // 20 bytes => 32 base32 characters, no padding
        expect_match($secret, '/^[A-Z2-7]{32}$/');
    }

    function it_generates_a_different_secret_each_time() {
        $first = $this->toString()->getWrappedObject();
        $this->flushAuth();
        $second = $this->toString()->getWrappedObject();

        if ($first === $second) {
            throw new \Exception('Secret was not cycled by flushAuth()');
        }
    }

    function it_accepts_a_valid_code() {
        $code = $this->makeTwoFactorCode()->getWrappedObject();

        $this->validateCode($code)->shouldReturn(true);
    }

    function it_rejects_an_incorrect_code() {
        $code = $this->makeTwoFactorCode()->getWrappedObject();
        $wrong = str_pad((string) (((int) $code + 1) % 1000000), 6, '0', STR_PAD_LEFT);

        $this->validateCode($wrong)->shouldReturn(false);
    }

    function it_rejects_a_non_numeric_code() {
        $this->validateCode('abcdef')->shouldReturn(false);
        $this->validateCode('')->shouldReturn(false);
    }

    function it_rejects_a_code_from_long_ago() {
        $old = $this->makeOTP()->getWrappedObject()->at(time() - 600);

        $this->validateCode($old)->shouldReturn(false);
    }

    function it_rejects_a_code_that_has_already_been_used() {
        $code = $this->makeTwoFactorCode()->getWrappedObject();

        $this->validateCode($code)->shouldReturn(true);
        // replay of the same code must fail while it is still within its validity window
        $this->validateCode($code)->shouldReturn(false);
        $this->validateCode($code)->shouldReturn(false);
    }

    function it_rejects_a_replay_even_with_a_wider_window() {
        $code = $this->makeTwoFactorCode()->getWrappedObject();

        $this->validateCode($code, 1)->shouldReturn(true);
        $this->validateCode($code, 1)->shouldReturn(false);
    }

    function it_does_not_burn_a_valid_code_when_a_wrong_one_is_submitted() {
        $code = $this->makeTwoFactorCode()->getWrappedObject();
        $wrong = str_pad((string) (((int) $code + 1) % 1000000), 6, '0', STR_PAD_LEFT);

        $this->validateCode($wrong)->shouldReturn(false);
        $this->validateCode($code)->shouldReturn(true);
    }

    function it_rejects_a_replay_through_a_second_instance_sharing_the_cache() {
        $code = $this->makeTwoFactorCode()->getWrappedObject();
        $this->validateCode($code)->shouldReturn(true);

        // same secret, fresh object (e.g. a later request); the cache is the only shared state
        $other = new TwoFactorAuthentication(null);
        $other->flushAuth();
        $reflection = new \ReflectionProperty(TwoFactorAuthentication::class, 'shared_secret');
        $reflection->setAccessible(true);
        $reflection->setValue($other, $this->toString()->getWrappedObject());

        if ($other->validateCode($code) !== false) {
            throw new \Exception('Used code was accepted again by a second instance');
        }
    }

    function it_uses_the_otpauth_uri_scheme() {
        $this->setLabel('user@example.com');

        $this->toUri()->shouldMatch('#^otpauth://totp/.+\?.*secret=[A-Z2-7]+#');
        $this->toUri()->shouldContain('user@example.com');
    }
}

function expect_match(string $subject, string $pattern) {
    if (!preg_match($pattern, $subject)) {
        throw new \Exception("'$subject' does not match $pattern");
    }
}
