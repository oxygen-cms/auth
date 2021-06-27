<?php

namespace Oxygen\Auth\Entity;

use DarkGhostHunter\Laraguard\Contracts\TwoFactorAuthenticatable;
use DarkGhostHunter\Laraguard\DoctrineTwoFactorAuthentication;
use Doctrine\ORM\Mapping AS ORM;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Contracts\Auth\Authenticatable as LaravelAuthenticable;
use Illuminate\Contracts\Auth\CanResetPassword;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\MessageBag;
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
use Oxygen\Preferences\Repository;
use Oxygen\Data\Behaviour\Searchable;
use Illuminate\Support\Facades\Notification;

/**
 * @ORM\Entity
 * @ORM\Table(name="users")
 * @ORM\HasLifecycleCallbacks
 */

class User implements PrimaryKeyInterface, Validatable, LaravelAuthenticable, CanResetPassword, Searchable, TwoFactorAuthenticatable {

    use PrimaryKey, Timestamps, SoftDeletes, Authentication, Permissions, Preferences;
    use \LaravelDoctrine\ORM\Notifications\Notifiable;
    use Accessors, Fillable;

    use DoctrineTwoFactorAuthentication;

    /**
     * @ORM\Column(type="string", unique=true)
     */
    protected $username;

    /**
     * @ORM\Column(name="full_name", type="string")
     */
    protected $fullName;

    /* protected $email; <--- exists inside the `RememberToken` trait */

    /**
     * @ORM\ManyToOne(targetEntity="Oxygen\Auth\Entity\Group", inversedBy="users", fetch="EAGER", cascade={"persist"})
     */
    protected $group;

    /**
     * @ORM\OneToMany(targetEntity="Oxygen\Auth\Entity\AuthenticationLogEntry", mappedBy="user")
     */
    protected $authenticationLogEntries;

    /**
     * True if all fields should be fillable (only for Administrators)
     *
     * @var boolean
     */
    protected $allFillable;

    /**
     * Returns a new preferences repository from the given preferences.
     *
     * @return Repository
     * @throws \Exception
     */
    public function createPreferencesRepository() {
        $this->createJsonTransformer();
        $repository = static::$jsonTransformer->toRepository($this->preferences);
        if($this->group != null) {
            $repository->addFallbackRepository($this->group->getPreferences());
        }
        return $repository;
    }

    /**
     * Sets whether all fields should be fillable.
     *
     * @param boolean $fillable
     */
    public function setAllFillable($fillable) {
        $this->allFillable = $fillable;
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
                'required',
                'json'
            ]
        ];
    }

    /**
     * Returns the fields that should be fillable.
     *
     * @return array
     */
    public function getFillableFields(): array {
        if($this->allFillable) {
            return ['username', 'fullName', 'email', 'preferences', 'group'];
        } else {
            return ['username', 'fullName', 'email'];
        }
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
     * Converts this model to JSON-equivalent array form suitable for returning from API endpoints.
     *
     * @return array
     */
    public function toArray() {
        $preferencesRepo = $this->getPreferences();

        return [
            'id' => $this->id,
            'username' => $this->username,
            'fullName' => $this->fullName,
            'email' => $this->email,
            'preferences' => $preferencesRepo->toArray(),
            'permissions' => $this->group->getPermissions(),
            'group' => $this->group->toArray(),
            'createdAt' => $this->createdAt !== null ? $this->createdAt->format(\DateTime::ATOM) : null,
            'updatedAt' => $this->updatedAt !== null ? $this->updatedAt->format(\DateTime::ATOM) : null
        ];
    }
}
