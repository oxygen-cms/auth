<?php


namespace Oxygen\Auth\Entity;

use DateTime;
use Doctrine\ORM\Mapping AS ORM;
use Oxygen\Data\Behaviour\Accessors;
use Oxygen\Data\Behaviour\PrimaryKey;

/**
 * @method int getType()
 * @method void setIpAddress(string $address)
 * @method void setUserAgent(string $userAgent)
 * @method void setUser(?User $user)
 * @method void setUsername(?string $username)
 * @method void setType(int $type)
 * @method DateTime getTimestamp()
 * @method string getIpAddress()
 * @method string getUserAgent()
 *
 * @ORM\Entity
 * @ORM\Table(name="auth_log")
 */
class AuthenticationLogEntry {

    use PrimaryKey, Accessors;

    /**
     * The login has succeded.
     *
     * @var string
     */
    const LOGIN_SUCCESS = 0;

    /**
     * The login has failed.
     *
     * @var string
     */
    const LOGIN_FAILED = 1;

    /**
     * The logout has succeded.
     *
     * @var string
     */
    const LOGOUT = 2;

    /**
     * @ORM\ManyToOne(targetEntity="Oxygen\Auth\Entity\User", inversedBy="authenticationLogEntries")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=true, onDelete="CASCADE")
     */
    protected $user;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $username;

    /**
     * @ORM\Column(type="text")
     */
    protected $userAgent;

    /**
     * @ORM\Column(type="string")
     */
    protected $ipAddress;

    /**
     * @ORM\Column(type="datetime")
     */
    protected $timestamp;

    /**
     * @ORM\Column(type="smallint")
     */
    protected $type;

    /**
     * Constructs a log entry.
     */
    public function __construct() {
        $this->timestamp = new \DateTime();
    }
    
    public function toArray() {
        return [
            'user' => $this->user ? $this->user->getId() : null,
            'userAgent' => $this->userAgent,
            'ipAddress' => $this->ipAddress,
            'timestamp' => $this->timestamp->format(\DateTime::ATOM),
            'type' => $this->type
        ];
    }

}