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
            ],
            'preferences' => [
                'required',
                'json'
            ],
            'permissions' => [
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

    protected function getFillableFields() {
        return ['name', 'description', 'preferences', 'permissions'];
    }

    /**
     * Returns the group's permissions.
     *
     * @return array
     * @throws \RuntimeException if user permissions couldn't be decoded.
     */
    public function getPermissions() {
        $res = json_decode($this->permissions, true);
        if(json_last_error() !== JSON_ERROR_NONE) {
            throw new \RuntimeException("Could Not Decode User Permissions: " . json_last_error_msg());
        }
        return $res;
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

    /**
     * Returns information about a group.
     *
     * @return array
     */
    public function toArray() {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description
        ];
    }

}
