<?php

namespace Oxygen\Auth\Preferences;

use Illuminate\Contracts\Auth\Guard;
use Illuminate\Support\Arr;
use Oxygen\Auth\Entity\User;
use Oxygen\Auth\Repository\UserRepositoryInterface;
use Oxygen\Data\Exception\InvalidEntityException;
use Oxygen\Preferences\ChainedStore;
use Oxygen\Preferences\Loader\LoaderInterface;
use Oxygen\Preferences\PreferencesSettingInterface;
use Webmozart\Assert\Assert;

class UserLoader implements LoaderInterface, PreferencesSettingInterface {

    /**
     * User repository.
     *
     * @var UserRepositoryInterface
     */
    protected UserRepositoryInterface $users;

    /**
     * @var null|ChainedStore
     */
    private ?ChainedStore $preferencesRepository;

    private Guard $auth;
    private ?string $prefix;

    /**
     * Constructs the UserLoader.
     *
     * @param UserRepositoryInterface $users
     * @param Guard $auth
     * @param string|null $prefix
     */
    public function __construct(UserRepositoryInterface $users, Guard $auth, string $prefix = null) {
        $this->users = $users;
        $this->auth = $auth;
        $this->preferencesRepository = null;
        $this->prefix = $prefix;
    }

    /**
     * Loads the preferences from the current users' record, and returns the repository.
     *
     * @return ChainedStore
     */
    public function load(): ChainedStore {
        if($this->preferencesRepository === null) {
            // we look through the User's preferences, then the Group's preferences,
            // then successive parent groups. Until we find the preference item which we want.
            $chain = function() {
                $prefs = $this->getUser()->getPreferences();
                yield Arr::get($prefs, $this->prefix, []);
                $group = $this->getUser()->getGroup();
                while($group !== null) {
                    yield Arr::get($group->getPreferences(), $this->prefix, []);
                    $group = $group->getParent();
                }
            };

            $this->preferencesRepository = new ChainedStore($chain, $this);
        }
        return $this->preferencesRepository;
    }

    /**
     * Stores the preferences in the database.
     *
     * @return void
     * @throws InvalidEntityException
     */
    public function store() {
        $user = $this->getUser();
        $this->users->persist($user);
    }

    /**
     * Sets the preferences value.
     *
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public function set(string $key, $value) {
        $user = $this->getUser();
        $prefs = $user->getPreferences();
        Arr::set($prefs, ltrim($this->prefix . '.' . $key, '.'), $value);
        $user->setPreferences($prefs);
    }

    private function getUser(): User {
        $user = $this->auth->user();
        Assert::isInstanceOf($user, User::class);
        return $user;
    }

}
