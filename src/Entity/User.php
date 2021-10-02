<?php

namespace Oxygen\Auth\Entity;

use DarkGhostHunter\Laraguard\Contracts\TwoFactorAuthenticatable;
use DarkGhostHunter\Laraguard\DoctrineTwoFactorAuthentication;
use DateTimeInterface;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping AS ORM;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Contracts\Auth\Authenticatable as LaravelAuthenticable;
use Illuminate\Contracts\Auth\CanResetPassword;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\MessageBag;
use LaravelDoctrine\ORM\Contracts\UrlRoutable;
use LaravelDoctrine\ORM\Notifications\Notifiable;
use Oxygen\Auth\Permissions\OwnedByUser;
use Oxygen\Auth\Permissions\Permissions;
use Oxygen\Auth\Preferences\Preferences;
use Oxygen\Data\Behaviour\Accessors;
use Oxygen\Data\Behaviour\Fillable;
use Oxygen\Data\Behaviour\PrimaryKey;
use Oxygen\Data\Behaviour\PrimaryKeyInterface;
use Oxygen\Data\Behaviour\Timestamps;
use Oxygen\Data\Behaviour\SoftDeletes;
use Oxygen\Data\Exception\InvalidEntityException;
use Oxygen\Data\Validation\Validatable;
use Oxygen\Data\Behaviour\Authentication;
use Oxygen\Data\Behaviour\Searchable;
use Illuminate\Support\Facades\Notification;
use DateTime;
use Illuminate\Auth\Notifications\VerifyEmail;

/**
 * @method string getEmail()
 * @method string getUsername()
 * @method string|null getFullName()
 * @method Collection getAuthenticationLogEntries()
 * @method void setFullName(string $fullName)
 *
 * @ORM\Entity
 * @ORM\Table(name="users")
 * @ORM\HasLifecycleCallbacks
 */
class User implements PrimaryKeyInterface, Validatable, LaravelAuthenticable, CanResetPassword, Searchable, TwoFactorAuthenticatable, MustVerifyEmail, UrlRoutable, OwnedByUser {

    use PrimaryKey, Timestamps, SoftDeletes, Authentication, Preferences;
    use Notifiable;
    use Accessors, Fillable;
    use Notifiable;

    use DoctrineTwoFactorAuthentication;

    /**
     * @ORM\Column(type="string", unique=true)
     */
    protected $username;

    /**
     * @ORM\Column(name="full_name", type="string")
     */
    protected $fullName;

    /**
     * @ORM\ManyToOne(inversedBy="users", fetch="EAGER", cascade={"persist"})
     */
    protected Group $group;

    /**
     * @ORM\OneToMany(targetEntity="Oxygen\Auth\Entity\AuthenticationLogEntry", mappedBy="user", cascade={"persist", "remove"})
     */
    protected $authenticationLogEntries;

    /**
     * @ORM\Column(name="email_verified_at", type="datetime", nullable=true)
     * @var DateTimeInterface
     */
    protected $verifiedAt;

    /**
     * Initializes default model values.
     */
    public function __construct() {
        $this->createdAt = new \DateTime();
        $this->updatedAt = new \DateTime();
    }

    /**
     * Returns an array of validation rules used to validate the model.
     *
     * @return array
     */
    public function getValidationRules() {
        $class = get_class($this);
        $id = $this->getId();

        return [
            'username' => [
                'required',
                'min:4',
                'max:50',
                'alpha_num',
                'unique:' . "$class,username,$id"
            ],
            'fullName' => [
                'required',
                'min:3',
                'max:255',
                'name'
            ],
            'email' => [
                'required',
                'email',
                'max:255'
            ],
            'preferences' => [
            ]
        ];
    }

    /**
     * Returns the fields that should be fillable.
     *
     * @return array
     */
    public function getFillableFields(): array {
        return ['username', 'fullName', 'email', 'group'];
    }

    /**
     * Updates the user's password.
     *
     * @param string $password
     * @return $this
     * @throws InvalidEntityException
     */
    public function setPassword($password) {
        if($password == null || trim($password) == '') {
            $errors = new MessageBag();
            $errors->add('password', 'The password field cannot be empty');
            throw new InvalidEntityException($this, $errors);
        }
        $this->password = Hash::make($password);
        return $this;
    }

    /**
     * Returns the fields that should be searched.
     *
     * @return array
     */
    public static function getSearchableFields() {
        return ['username', 'fullName'];
    }

    /**
     * Get the e-mail address where password reset links are sent.
     *
     * @return string
     */
    public function getEmailForPasswordReset() {
        return $this->email;
    }

    /**
     * @inheritDoc
     */
    public function sendPasswordResetNotification($token) {
        Notification::send([$this], new ResetPassword($token));
    }

    /**
     * Returns true if this user should be allowed to impersonate other users.
     *
     * @return bool
     */
    public function canImpersonate(): bool {
        return app(Permissions::class)->hasForUser($this, 'auth.impersonate');
    }

    /**
     * Return true or false if the user can be impersonated.
     *
     * @return bool
     */
    public function canBeImpersonated(): bool {
        return true;
    }

    /**
     * Returns a flat set of preferences which have been merged together already.
     *
     * @return array
     */
    public function getMergedPreferences(): array {
        $prefs = [$this->getPreferences()];
        $group = $this->getGroup();
        while($group !== null) {
            $prefs[] = $group->getPreferences();
            $group = $group->getParent();
        }
        return array_merge_recursive_ignore_null(...array_reverse($prefs));
    }

    /**
     * Converts this model to JSON-equivalent array form suitable for returning from API endpoints.
     *
     * @return array
     */
    public function toArray() {
        return [
            'id' => $this->id,
            'username' => $this->username,
            'fullName' => $this->fullName,
            'email' => $this->email,
            'preferences' => $this->getMergedPreferences(),
            'permissions' => $this->group->getMergedPermissions(),
            'group' => $this->group->toArray(),
            'createdAt' => $this->createdAt->format(DateTimeInterface::ATOM),
            'updatedAt' => $this->updatedAt->format(DateTimeInterface::ATOM),
            'deletedAt' => $this->deletedAt !== null ? $this->deletedAt->format(DateTimeInterface::ATOM) : null,
            'emailVerified' => $this->hasVerifiedEmail(),
            'twoFactorAuthEnabled' => $this->hasTwoFactorEnabled()
        ];
    }

    public function getGroup(): Group {
        return $this->group;
    }

    /**
     * Determine if the user has verified their email address.
     *
     * @return bool
     */
    public function hasVerifiedEmail() {
        return $this->verifiedAt !== null && $this->verifiedAt < new DateTime();
    }

    /**
     * Mark the given user's email as verified.
     *
     * @return bool
     */
    public function markEmailAsVerified() {
        $this->verifiedAt = new DateTime();
        return true;
    }

    /**
     * Send the email verification notification.
     *
     * @return void
     */
    public function sendEmailVerificationNotification() {
        $this->notify(new VerifyEmail());
    }

    /**
     * Get the email address that should be used for verification.
     *
     * @return string
     */
    public function getEmailForVerification() {
        return $this->email;
    }

    /**
     * Required for email verification notifications
     *
     * @return int
     */
    public function getKey() {
        return $this->getId();
    }

    /**
     * @return $this
     */
    public function getOwner(): User {
        return $this;
    }

    /**
     * @param int|Group $group
     * @throws InvalidEntityException
     */
    public function setGroup($group): void {
        if(is_integer($group)) {
            $group = app(EntityManager::class)->getReference(Group::class, $group);
        }
        if(is_null($group)) {
            throw new InvalidEntityException($this, new MessageBag(['group' => 'Group is required.']));
        }
        $this->group = $group;
    }

}
