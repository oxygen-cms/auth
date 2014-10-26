<?php

namespace Oxygen\Auth\Model;

use Hash;

use Illuminate\Auth\UserTrait as BaseUserTrait;
use Illuminate\Auth\Reminders\RemindableTrait;
use Illuminate\Auth\UserInterface;
use Illuminate\Auth\Reminders\RemindableInterface;

use Oxygen\Core\Model\Resource;
use Oxygen\Core\Model\SoftDeleting\SoftDeletingTrait;
use Oxygen\Auth\Permissions\PermissionsTrait;
use Oxygen\Auth\Preferences\PreferencesTrait;

class User extends Resource implements UserInterface, RemindableInterface {

    use BaseUserTrait, RemindableTrait, SoftDeletingTrait, PermissionsTrait, PreferencesTrait;

    /**
     * Constructs a new User.
     *
     * @param array $attributes
     * @return void
     */

    public function __construct($attributes = array()) {
        parent::__construct($attributes, 'Auth');
    }

    /**
     * Listens to the `saving` event and that hashes the User's password.
     *
     * @return void
     */

    public static function boot() {
        parent::boot();

        static::saving(function($model) {
            if($model->isDirty('password')) {
                $model->password = Hash::make($model->password);
            }

            return true;
        });
    }

    /**
     * Returns a new preferences repository from the given preferences.
     *
     * @return Repository
     */

    public function createPreferencesRepository() {
        $repository = static::$jsonTransformer->toRepository($this->preferences);
        $repository->addFallbackRepository($this->group->getPreferences());
        return $repository;
    }

    /**
     * Group relationship
     *
     * @return Illuminate\Database\Eloquent\Relations\BelongsTo
     */

    public function group() {
        return $this->belongsTo('Oxygen\Auth\Model\Group');
    }

}
