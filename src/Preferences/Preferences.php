<?php

namespace Oxygen\Auth\Preferences;

use Oxygen\Preferences\Repository;
use Oxygen\Preferences\Transformer\JsonTransformer;

trait Preferences {

    /**
     * @ORM\Column(type="text")
     */

    protected $preferences;

    /**
     * JsonTransformer loads preferences from a JSON string.
     *
     * @var JsonTransformer
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
    public function setPreferencesRepository(Repository $repository) {
        $this->preferencesRepository = $repository;
    }

    /**
     * Sets the raw preferences field.
     *
     * @param string $preferences
     * @return $this
     */
    public function setPreferences($preferences) {
        $this->preferences = $preferences;
        $this->preferencesRepository = $this->createPreferencesRepository();
        return $this;
    }

    /**
     * Returns a new preferences repository from the given preferences.
     *
     * @return Repository
     */
    public function createPreferencesRepository() {
        $this->createJsonTransformer();
        try {
            return static::$jsonTransformer->toRepository($this->preferences);
        } catch(\Exception $e) {
            logger()->warning('Exception while creating entity preferences repository, ' . $e->getMessage());
            return new Repository([]);
        }
    }

    /**
     * Sync the preferences repository back with the model's `preferences` attributew.
     *
     * @return void
     */
    public function syncPreferences() {
        $this->createJsonTransformer();
        $this->preferences = static::$jsonTransformer->fromRepository($this->getPreferences(), true);
    }

    /**
     * Creates the json transformer if needed.
     *
     * @return void
     */

    protected function createJsonTransformer() {
        if(static::$jsonTransformer === null) {
            static::$jsonTransformer = new JsonTransformer();
        }
    }

}