<?php

namespace Oxygen\Auth\Preferences;

trait Preferences {

    /**
     * @ORM\Column(type="json")
     */
    protected $preferences;

    /**
     * Returns the preferences repository.
     * Creates a new repository if one doesn't already exist.
     *
     * @return array
     */
    public function getPreferences(): array {
        return $this->preferences;
    }

    /**
     * Sets the raw preferences field.
     *
     * @param string|array $preferences
     * @return $this
     */
    public function setPreferences($preferences) {
        $this->preferences = $preferences;
        return $this;
    }

}
