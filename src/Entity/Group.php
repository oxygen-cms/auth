<?php

namespace Oxygen\Auth\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping AS ORM;
use Oxygen\Auth\Permissions\Permissions;
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
     * @ORM\OneToMany(targetEntity="Oxygen\Auth\Entity\User", mappedBy="group")
     */
    protected $users;

    /**
     * @ORM\Column(type="json", nullable=true)
     */
    protected $permissions;

    /**
     * @ORM\ManyToOne(inversedBy="children", fetch="LAZY", cascade={"persist"})
     * @ORM\JoinColumn(name="parent_id", nullable=true)
     */
    protected ?Group $parent;

    /**
     * @ORM\OneToMany(targetEntity="Oxygen\Auth\Entity\Group", mappedBy="parent")
     */
    protected $children;

    /**
     * Constructs the Group.
     */
    public function __construct() {
        $this->users = new ArrayCollection();
        $this->children = new ArrayCollection();
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
            ],
            'permissions' => [
                'required',
            ]
        ];
    }

    /**
     * Returns the fields that should be fillable.
     *
     * @return array
     */
    public function getFillableFields(): array {
        return ['name', 'description', 'preferences', 'permissions', 'parent'];
    }

    /**
     * Returns the group's permissions.
     *
     * @return array
     * @throws \RuntimeException if user permissions couldn't be decoded.
     */
    public function getPermissions() {
        return $this->permissions;
    }

    /**
     * Sets the permissions.
     *
     * @param  array|string $permissions
     * @return $this
     */
    public function setPermissions($permissions) {
        $this->permissions = $permissions;
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

    /**
     * @return null|Group
     */
    public function getParent(): ?Group {
        return $this->parent;
    }

    /**
     * @param null|Group $parent
     */
    public function setParent(?Group $parent) {
        $this->parent = $parent;
    }

    /**
     * Returns this Group's permissions, combined with all inherited permissions as well.
     *
     * @return array
     */
    public function getMergedPermissions(): array {
        $perms = [];
        $group = $this;
        while($group !== null) {
            $perms[] = $group->getPermissions();
            $group = $group->getParent();
        }
        return array_merge_recursive_ignore_null(...array_reverse($perms));
    }

}
