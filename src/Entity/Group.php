<?php

namespace Oxygen\Auth\Entity;

use DateTime;
use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping AS ORM;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;
use Oxygen\Auth\Permissions\Permissions;
use Oxygen\Auth\Permissions\PermissionsSource;
use Oxygen\Auth\Permissions\TreePermissionsSystem;
use Oxygen\Auth\Preferences\Preferences;
use Oxygen\Data\Behaviour\Accessors;
use Oxygen\Data\Behaviour\Fillable;
use Oxygen\Data\Behaviour\PrimaryKey;
use Oxygen\Data\Behaviour\PrimaryKeyInterface;
use Oxygen\Data\Behaviour\Timestamps;
use Oxygen\Data\Behaviour\SoftDeletes;
use Oxygen\Data\Validation\Rules\Unique;
use Oxygen\Data\Validation\Validatable;
use Oxygen\Data\Behaviour\Searchable;

/**
 * @method string|null getDescription()
 * @method string|null getIcon()
 * @method Collection getUsers()
 * @method Collection getChildren()
  *
 * @ORM\Entity
 * @ORM\Table(name="`groups`")
 * @ORM\HasLifecycleCallbacks
 */
class Group implements Validatable, PrimaryKeyInterface, Searchable, PermissionsSource, Arrayable {

    use PrimaryKey, Accessors, Timestamps, SoftDeletes, Fillable, Preferences;

    /**
     * @ORM\Column(type="string")
     */
    protected $name;

    /**
     * TODO: add `unique=true` at a later stage
     * @ORM\Column(type="string")
     */
    protected $nickname;

    /**
     * @ORM\Column(type="string", length=30)
     */
    protected $icon;

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
        $this->createdAt = new DateTime();
        $this->updatedAt = new DateTime();
    }

    /**
     * Returns an array of validation rules used to validate the model.
     *
     * @return array
     */
    public function getValidationRules() {
        $unique = Unique::amongst(get_class($this))->field('nickname')->ignoreWithId($this->getId());
        return [
            'name' => [
                'required',
                'max:255'
            ],
            'nickname' => [
                $unique
            ],
            'preferences' => [
                'required',
            ],
            'permissions' => [
            ]
        ];
    }

    /**
     * Returns the fields that should be fillable.
     *
     * @return array
     */
    public function getFillableFields(): array {
        return ['name', 'description', 'nickname', 'parent', 'icon'];
    }

    /**
     * Returns the group's permissions.
     *
     * @return array
     */
    public function getPermissions(): array {
        return $this->permissions;
    }

    /**
     * Sets the permissions.
     *
     * @param  array $permissions
     * @return $this
     */
    public function setPermissions(array $permissions) {
        $this->cleanPermissions($permissions);
        $this->permissions = $permissions;
        return $this;
    }

    /**
     * @param array $permissions
     */
    private function cleanPermissions(array &$permissions) {
        foreach($permissions as $contentType => $actions) {
            if(count($actions) === 0) {
                Arr::forget($permissions, $contentType);
            }
        }
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
            'nickname' => $this->nickname,
            'description' => $this->description,
            'icon' => $this->icon,
            'createdAt' => $this->createdAt->format(DateTimeInterface::ATOM),
            'updatedAt' => $this->updatedAt->format(DateTimeInterface::ATOM),
            'deletedAt' => $this->deletedAt !== null ? $this->deletedAt->format(DateTimeInterface::ATOM) : null
        ];
    }

    /**
     * @return null|Group
     */
    public function getParent(): ?Group {
        return $this->parent;
    }

    /**
     * @param null|int|Group $parent
     */
    public function setParent($parent) {
        if(is_integer($parent)) {
            $parent = app(EntityManagerInterface::class)->getReference(Group::class, $parent);
        }
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

    public function getFlatPermissions(): array {
        $perms = [];
        foreach($this->getPermissions() as $contentType => $actions) {
            foreach ($actions as $action => $value) {
                if($action === TreePermissionsSystem::PARENT_KEY) {
                    continue;
                }
                $perms[] = $contentType . '.' . $action;
            }
        }
        return $perms;
    }

    /**
     * Returns the content types referenced by this group.
     * @return array
     */
    public function getPermissionContentTypes(): array {
        return array_keys($this->getPermissions());
    }

    /**
     * @return string
     */
    public function getName(): string {
        return $this->name;
    }

    public function getNickname() {
        return $this->nickname;
    }

    /**
     * Grants the specified permissions to the group.
     *
     * @param string $key
     */
    public function grantPermissions(string $key) {
        $permissions = $this->getPermissions();
        Arr::set($permissions, $key, true);
        $this->setPermissions($permissions);
    }

    /**
     * Explicitly denies the specified permission for the group.
     *
     * @param string $key
     */
    public function denyPermissions(string $key) {
        $permissions = $this->getPermissions();
        Arr::set($permissions, $key, false);
        $this->setPermissions($permissions);
    }

    /**
     * Unsets denies the specified permission for the group, reverting it to a default value.
     *
     * @param string $key
     */
    public function unsetPermissions(string $key) {
        $permissions = $this->getPermissions();
        Arr::forget($permissions, $key);
        $this->setPermissions($permissions);
    }

    /**
     * @param string $contentType
     * @param string|null $parentContentType
     */
    public function setPermissionInheritance(string $contentType, ?string $parentContentType) {
        $permissions = $this->getPermissions();
        $key = $contentType . '.' . TreePermissionsSystem::PARENT_KEY;
        if($parentContentType !== null) {
            Arr::set($permissions, $key, $parentContentType);
        } else {
            Arr::forget($permissions, $key);
        }
        $this->setPermissions($permissions);
    }
}
