<?php

namespace Oxygen\Auth\Entity;

use Doctrine\ORM\Mapping AS ORM;
use LaravelDoctrine\ORM\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\CanResetPassword;
use Illuminate\Support\Facades\Hash;
use Oxygen\Auth\Permissions\Permissions;
use Oxygen\Auth\Preferences\Preferences;
use Oxygen\Data\Behaviour\Accessors;
use Oxygen\Data\Behaviour\Fillable;
use Oxygen\Data\Behaviour\PrimaryKey;
use Oxygen\Data\Behaviour\PrimaryKeyInterface;
use Oxygen\Data\Behaviour\Timestamps;
use Oxygen\Data\Behaviour\SoftDeletes;
use Oxygen\Data\Validation\Validatable;
use Oxygen\Data\Behaviour\Authentication;
use Oxygen\Preferences\Repository;

/**
 * @ORM\Entity
 * @ORM\Table(name="users")
 * @ORM\HasLifecycleCallbacks
 */

class User implements PrimaryKeyInterface, Validatable, Authenticatable, CanResetPassword {

    use PrimaryKey, Timestamps, SoftDeletes, Authentication, Permissions, Preferences;
    use Accessors, Fillable;

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
     * @ORM\ManyToOne(targetEntity="Oxygen\Auth\Entity\Group", inversedBy="users", fetch="EAGER", cascade="persist")
     */

    protected $group;

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
     */
    public function createPreferencesRepository() {
        $this->createJsonTransformer();
        $repository = static::$jsonTransformer->toRepository($this->preferences);
        if($this->getGroup() != null) {
            $repository->addFallbackRepository($this->getGroup()->getPreferences());
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
            ]
        ];
    }

    /**
     * Returns the fields that should be fillable.
     *
     * @return array
     */

    protected function getFillableFields() {
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
     */
    public function setPassword($password) {
        $this->password = Hash::make($password);
        return $this;
    }

    /**
     * Get the e-mail address where password reset links are sent.
     *
     * @return string
     */
    public function getEmailForPasswordReset() {
        return $this->email;
    }
}
