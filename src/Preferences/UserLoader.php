<?php

namespace Oxygen\Auth\Preferences;

use Oxygen\Auth\Model\User;
use Oxygen\Preferences\Loader\LoaderInterface;
use Oxygen\Preferences\Repository;
use Oxygen\Preferences\Schema;

class UserLoader implements LoaderInterface {

    /**
     * User model.
     *
     * @var User
     */

    protected $user;

    /**
     * Constructs the UserLoader.
     *
     * @param User $user User model
     */

    public function __construct(User $user = null) {
        $this->user = $user;
    }

    /**
     * Loads the preferences and returns the repository.
     *
     * @return Repository
     */

    public function load() {
        return $this->user->getPreferences();
    }

    /**
     * Stores the preferences.
     *
     * @param Repository $repository
     * @return void
     */

    public function store(Repository $preferences, Schema $schema) {
        $this->user->setPreferences($preferences);
        $this->user->syncPreferences();
        $this->user->save();
    }

}
