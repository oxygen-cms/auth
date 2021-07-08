<?php

namespace Oxygen\Auth\Preferences;

use Illuminate\Contracts\Auth\Guard;
use Illuminate\Support\Arr;
use Oxygen\Auth\Entity\User;
use Oxygen\Auth\Repository\UserRepositoryInterface;
use Oxygen\Preferences\ChainedStore;
use Oxygen\Preferences\Loader\LoaderInterface;

class UserLoader implements LoaderInterface {

    /**
     * User repository.
     *
     * @var UserRepositoryInterface
     */
    protected $users;


    /**
     * @var null|ChainedStore
     */
    private $preferencesRepository;

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
                $prefs = $this->auth->user()->getPreferences();
                yield Arr::get($prefs, $this->prefix, []);
                $group = $this->auth->user()->getGroup();
                while($group !== null) {
                    yield Arr::get($group->getPreferences(), $this->prefix, []);
                    $group = $group->getParent();
                }
            };

            $this->preferencesRepository = new ChainedStore($chain);
        }
        return $this->preferencesRepository;
    }

    /**
     * Stores the preferences in the database.
     *
     * @return void
     */
    public function store() {
        if($this->preferencesRepository !== null) {
            $user = $this->auth->user();

            $original = Arr::get($user->getPreferences(), $this->prefix, []);
            $changed = $this->preferencesRepository->getChangedArray();
            $new = array_merge_recursive_distinct($original, $changed);
            $prefs = $user->getPreferences();
            Arr::set($prefs, $this->prefix, $new);
            $user->setPreferences($prefs);
            $this->users->persist($user);
        }
    }

}
