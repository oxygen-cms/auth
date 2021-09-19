<?php

namespace Oxygen\Auth\Entity;

use Doctrine\ORM\Mapping AS ORM;
use Carbon\Carbon;
use DateTime;

/**
 * @ORM\Entity
 * @ORM\Table(name="sessions")
 */
class DoctrineSession {

    /**
     * @ORM\Id
     * @ORM\Column(type="string")
     */
    private ?string $id;

    /**
     * @ORM\ManyToOne
     * @ORM\JoinColumn(name="user_id", onDelete="CASCADE")
     */
    protected ?User $user;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $ipAddress;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    protected $userAgent;

    /**
     * @ORM\Column(type="text", nullable=false)
     */
    protected $payload;

    /**
     * @ORM\Column(type="datetime", nullable=false)
     */
    protected DateTime $lastActivity;

    /**
     * Create a session with an ID
     *
     * @param string|null $id ID to use
     */
    public function __construct(?string $id, string $payload) {
        $this->id = $id;
        $this->setPayload($payload);
    }

    /**
     * Returns true if the session is expired
     *
     * @param $minutes
     * @return bool
     */
    public function expired($minutes): bool {
        return new Carbon($this->lastActivity) < Carbon::now()->subMinutes($minutes);
    }

    /**
     * @return mixed
     */
    public function getPayload() {
        return base64_decode($this->payload);
    }

    /**
     * @return bool
     */
    public function hasPayload(): bool {
        return (bool) $this->payload;
    }

    private function setPayload(string $payload) {
        $this->payload = base64_encode($payload);
    }

    public function setUser(?User $user) {
        $this->user = $user;
    }

    public function setLastActivity(DateTime $lastActivity) {
        $this->lastActivity = $lastActivity;
    }

    /**
     * Returns the ID of the session.
     *
     * @return string
     */
    public function getId() {
        return $this->id;
    }

    /**
     * Clones the entity.
     *
     * @return void
     */
    public function __clone() {
        $this->id = null;
    }

    public function setIpAddress(string $ipAddress) {
        $this->ipAddress = $ipAddress;
    }

    public function setUserAgent(string $userAgent) {
        $this->userAgent = $userAgent;
    }

    /**
     * @return string
     */
    public function getIpAddress() {
        return $this->ipAddress;
    }

    /**
     * @return string
     */
    public function getUserAgent() {
        return $this->userAgent;
    }

    /**
     * @return DateTime
     */
    public function getLastActivity(): DateTime {
        return $this->lastActivity;
    }

    /**
     * @return User|null
     */
    public function getUser(): ?User {
        return $this->user;
    }

}