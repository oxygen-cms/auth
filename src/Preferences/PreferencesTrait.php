<?php

namespace Oxygen\Auth\Preferences;

use Oxygen\Preferences\Repository;
use Oxygen\Preferences\Transformer\JsonTransformer;

trait PreferencesTrait {

    /**
     * JsonLoader loads preferences from a JSON string.
     *
     * @var JsonLoader
     */

    protected static $jsonTransformer;

    /**
     *
     * Preferences repository encapsulates the storage and retrieval of preferences.
     *
     * @var Repository
     */

    protected $preferencesRepository;

    /**
     * Boot the preferences trait.
     *
     * @return void
     */

    public static function bootPreferencesTrait() {
        static::$jsonTransformer = new JsonTransformer();
    }

    /**
     * Returns the preferences repository.
     * Creates a new repository if one doesn't already exist.
     *
     * @return Repository
     */

    public function getPreferences() {
        if($this->preferencesRepository === null) {
            $this->preferencesRepository = $this->createPreferencesRepository();
        }

        return $this->preferencesRepository;
    }

    /**
     * Sets the preferences repository.
     *
     * @param Repository $repository
     * @return void
     */

    public function setPreferences(Repository $repository) {
        $this->preferencesRepository = $repository;
    }

    /**
     * Returns a new preferences repository from the given preferences.
     *
     * @return Repository
     */

    public function createPreferencesRepository() {
        return static::$jsonTransformer->toRepository($this->preferences);
    }

    /**
     * Sync the preferences repository back with the model's `preferences` attributew.
     *
     * @return void
     */

    public function syncPreferences() {
        $this->preferences = static::$jsonTransformer->fromRepository($this->getPreferences(), true);
    }

}