<?php

namespace Oxygen\Auth\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping AS ORM;
use Oxygen\Auth\Preferences\Preferences;
use Oxygen\Data\Behaviour\Accessors;
use Oxygen\Data\Behaviour\Fillable;
use Oxygen\Data\Behaviour\PrimaryKey;
use Oxygen\Data\Behaviour\PrimaryKeyInterface;
use Oxygen\Data\Behaviour\Timestamps;
use Oxygen\Data\Behaviour\SoftDeletes;
use Oxygen\Data\Validation\Validatable;
use Oxygen\Data\Behaviour\Searchable;

/**
 * @ORM\Entity
 * @ORM\Table(name="`groups`")
 * @ORM\HasLifecycleCallbacks
 */

class Group implements Validatable, PrimaryKeyInterface, Searchable {

    use PrimaryKey, Accessors, Timestamps, SoftDeletes, Fillable, Preferences;

    /**
     * @ORM\Column(type="string")
     */

    protected $name;

    /**
     * @ORM\Column(type="text", nullable=true)
     */

    protected $description;

    /**
     * @ORM\Column(type="text", nullable=true)
     */

    protected $permissions;

    /**
     * @ORM\OneToMany(targetEntity="Oxygen\Auth\Entity\User", mappedBy="group")
     */

    protected $users;

    /**
     * Constructs the Group.
     */
    public function __construct() {
        $this->users = new ArrayCollection();
    }

    /**
     * Returns an array of validation rules used to validate the model.
     *
     * @return array
     */
    public function getValidationRules() {
        return [
            'name' => [
                'required',
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
        return ['name', 'description', 'preferences', 'permissions'];
    }

    /**
     * Returns the group's permissions.
     *
     * @return array
     */
    public function getPermissions() {
        return json_decode($this->permissions, true);
    }

    /**
     * Sets the permissions.
     *
     * @param  array|string $permissions
     * @return $this
     */
    public function setPermissions($permissions) {
        $this->permissions = is_string($permissions) ? $permissions : json_encode($permissions, JSON_PRETTY_PRINT);
        return $this;
    }

    /**
     * Returns the fields that should be searched.
     *
     * @return array
     */
    public static function getSearchableFields() {
        return ['name', 'description'];
    }

}