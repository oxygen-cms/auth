<?php

namespace Oxygen\Auth\Preferences;

use Oxygen\Auth\Entity\User;
use Oxygen\Auth\Repository\UserRepositoryInterface;
use Oxygen\Preferences\Loader\LoaderInterface;
use Oxygen\Preferences\Repository;
use Oxygen\Preferences\Schema;

class UserLoader implements LoaderInterface {

    /**
     * User repository.
     *
     * @var UserRepositoryInterface
     */

    protected $repository;

    /**
     * User model.
     *
     * @var User
     */

    protected $user;

    /**
     * Constructs the UserLoader.
     *
     * @param UserRepositoryInterface $repository
     * @param User                    $user User model
     */

    public function __construct(UserRepositoryInterface $repository, User $user = null) {
        $this->repository = $repository;
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
     * @param Repository $preferences
     * @param Schema     $schema
     * @return void
     */

    public function store(Repository $preferences, Schema $schema) {
        $this->user->setPreferencesRepository($preferences);
        $this->user->syncPreferences();
        $this->repository->persist($this->user);
    }

}
