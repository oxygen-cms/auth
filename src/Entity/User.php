<?php

namespace Oxygen\Auth\Entity;

use Doctrine\ORM\Mapping AS ORM;
use Illuminate\Auth\UserInterface;
use Illuminate\Support\Facades\Hash;
use Oxygen\Auth\Permissions\Permissions;
use Oxygen\Auth\Preferences\Preferences;
use Oxygen\Data\Behaviour\Accessors;
use Oxygen\Data\Behaviour\Fillable;
use Oxygen\Data\Behaviour\PrimaryKey;
use Oxygen\Data\Behaviour\Timestamps;
use Oxygen\Data\Behaviour\SoftDeletes;
use Oxygen\Data\Validation\Validatable;
use Mitch\LaravelDoctrine\Traits\Authentication;
use Oxygen\Preferences\Repository;

/**
 * @ORM\Entity
 * @ORM\Table(name="users")
 * @ORM\HasLifecycleCallbacks
 */

class User implements Validatable, UserInterface {

    use PrimaryKey, Timestamps, SoftDeletes, Authentication, Permissions, Preferences;
    use Accessors, Fillable;

    /**
     * @ORM\Column(type="string")
     */

    protected $username;

    /**
     * @ORM\Column(name="full_name", type="string")
     */

    protected $fullName;

    /**
     * @ORM\Column(type="string")
     */

    protected $email;
    
    /**
     * @ORM\ManyToOne(targetEntity="Oxygen\Auth\Entity\Group", inversedBy="users", fetch="EAGER", cascade="persist")
     */

    protected $group;

    /**
     * Returns a new preferences repository from the given preferences.
     *
     * @return Repository
     */

    public function createPreferencesRepository() {
        $this->createJsonTransformer();
        $repository = static::$jsonTransformer->toRepository($this->preferences);
        $repository->addFallbackRepository($this->getGroup()->getPreferences());
        return $repository;
    }

    /**
     * Returns an array of validation rules used to validate the model.
     *
     * @return array
     */

    public function getValidationRules() {
        return [
            'username' => [
                'required',
                'min:4',
                'max:50',
                'alpha_num',
                'unique:' . __CLASS__ . ',username,' . $this->getId() . ',id,email,!=,foo@baz.dof'
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
        return ['username', 'fullName', 'email'];
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
     * Hack around DoctrineUserProvider. Should be removed as soon as fix is available.
     *
     * @return string
     */

    public function getKeyName() {
        return 'id';
    }

}
