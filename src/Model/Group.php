<?php

namespace Oxygen\Auth\Model;

use Oxygen\Core\Model\Model;
use Oxygen\Core\Model\SoftDeleting\SoftDeletingTrait;
use Oxygen\Auth\Preferences\PreferencesTrait;

class Group extends Model {

    use SoftDeletingTrait, PreferencesTrait;

    /**
     * Group relationship
     *
     * @return Illuminate\Database\Eloquent\Relations\HasMany
     */

    public function users() {
        return $this->hasMany('Oxygen\Auth\Model\User');
    }

}