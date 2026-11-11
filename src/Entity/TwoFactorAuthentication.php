<?php

namespace Oxygen\Auth\Entity;

use Doctrine\ORM\Mapping AS ORM;
use Oxygen\Auth\TwoFactor\Concerns\HandlesCodes;
use Oxygen\Auth\TwoFactor\Concerns\SerializesSharedSecret;
use Oxygen\Data\Behaviour\PrimaryKey;
use Oxygen\Data\Behaviour\Timestamps;
use ParagonIE\ConstantTime\Base32;
use Oxygen\Auth\TwoFactor\Contracts\TwoFactorTotp;

#[ORM\Entity]
#[ORM\Table(name: '`two_factor_auth`')]
#[ORM\HasLifecycleCallbacks]
class TwoFactorAuthentication implements TwoFactorTotp {
    use HandlesCodes;
    use SerializesSharedSecret;

    use PrimaryKey, Timestamps;

    #[ORM\OneToOne(targetEntity: 'Oxygen\Auth\Entity\User', inversedBy: 'twoFactorAuth', cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(name: 'authenticatable_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    protected $authenticatable;

    #[ORM\Column(type: 'string')]
    protected $shared_secret;

    /**
     * @var \DateTime|null
     */
    #[ORM\Column(name: 'enabled_at', type: 'datetime', nullable: true)]
    private $enabled_at;

    /**
     * @var string
     */
    #[ORM\Column(type: 'string')]
    private $label;

    #[ORM\Column(type: 'smallint')]
    private $digits;
    #[ORM\Column(type: 'smallint')]
    private $seconds;
    #[ORM\Column(type: 'smallint', name: '`window`')]
    private $window;

    #[ORM\Column(type: 'string', name: '`algorithm`')]
    private $algorithm;

    /*
     * Legacy columns: recovery codes and safe devices are no longer supported, but the columns are kept
     * (and cleared by flushAuth()) so the schema is unchanged.
     */

    #[ORM\Column(type: 'json', nullable: true)]
    protected $recovery_codes;

    /**
     * @var \DateTime|null
     */
    #[ORM\Column(name: 'recovery_codes_generated_at', type: 'datetime', nullable: true)]
    private $recovery_codes_generated_at; // @phpstan-ignore property.onlyWritten

    #[ORM\Column(type: 'json', nullable: true)]
    private $safe_devices; // @phpstan-ignore property.onlyWritten

    /**
     * TwoFactorAuthentication constructor.
     */
    public function __construct($authenticatable) {
        $this->authenticatable = $authenticatable;
        $this->digits = 6;
        $this->seconds = 30;
        $this->window = 0;
    }

    /**
     * The model that uses Two Factor Authentication.
     *
     * @return \Oxygen\Auth\Entity\User
     */
    public function authenticatable() {
        return $this->authenticatable;
    }

    public function setLabel(string $string) {
        $this->label = $string;
    }

    public function setEnabledAt(\DateTimeInterface $now) {
        $this->enabled_at = \DateTime::createFromInterface($now);
    }

    /**
     * Gets the Shared Secret attribute from its binary form.
     * @return null|string
     */
    protected function getSharedSecret() {
        return $this->shared_secret;
    }

    /**
     * Sets the Shared Secret attribute to its binary form.
     *
     * @param $value
     */
    protected function setSharedSecret($value) {
        $this->shared_secret = $value;
    }

    /**
     * Sets the Algorithm to lowercase.
     *
     * @param $value
     */
    protected function setAlgorithmAttribute($value) {
        $this->algorithm = strtolower($value);
    }

    /**
     * Returns if the Two Factor Authentication has been enabled.
     *
     * @return bool
     */
    public function isEnabled() {
        return $this->enabled_at !== null;
    }

    /**
     * Returns if the Two Factor Authentication is not been enabled.
     *
     * @return bool
     */
    public function isDisabled() {
        return !$this->isEnabled();
    }

    /**
     * Flushes all authentication data and cycles the Shared Secret.
     *
     * @return $this
     */
    public function flushAuth() {
        $this->recovery_codes = null;
        $this->recovery_codes_generated_at = null;
        $this->safe_devices = null;
        $this->enabled_at = null;

        $defaults = config('two-factor.totp');

        $this->digits = $defaults['digits'];
        $this->seconds = $defaults['seconds'];
        $this->window = $defaults['window'];
        $this->algorithm = $defaults['algorithm'];

        $this->shared_secret = static::generateRandomSecret();

        return $this;
    }

    /**
     * Creates a new Random Secret.
     *
     * @return string
     */
    public static function generateRandomSecret() {
        return Base32::encodeUpperUnpadded(random_bytes(config('two-factor.secret_length')));
    }
}
