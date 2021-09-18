<?php

namespace Oxygen\Auth\Permissions;

/**
 * Instead of just returning "permitted" or "denied", we can provide more
 * information about where inherited permissions were derived from.
 */
class PermissionsExplanation {

    private bool $permitted;
    private ?PermissionsSource $source;
    private ?string $contentType;
    private ?string $action;

    /**
     * @param bool $permitted
     * @param PermissionsSource|null $source if the permissions value came from a particular source
     * @param string|null $contentType
     * @param string|null $action
     */
    private function __construct(bool $permitted, ?PermissionsSource $source = null, ?string $contentType = null, ?string $action = null) {
        $this->permitted = $permitted;
        $this->source = $source;
        $this->contentType = $contentType;
        $this->action = $action;
    }

    /**
     * Permissions were granted
     *
     * @param bool $granted
     * @param PermissionsSource $source
     * @param string $contentType
     * @param string $action
     * @return PermissionsExplanation
     */
    public static function make(bool $granted, PermissionsSource $source, string $contentType, string $action): PermissionsExplanation {
        return new PermissionsExplanation($granted, $source, $contentType, $action);
    }

    /**
     * Permissions were explicitly denied
     * @return PermissionsExplanation
     */
    public static function deniedByDefault(): PermissionsExplanation {
        return new PermissionsExplanation(false);
    }

    /**
     * @return bool
     */
    public function isPermitted(): bool {
        return $this->permitted;
    }

    /**
     * @return null|PermissionsSource
     */
    public function getSource(): ?PermissionsSource {
        return $this->source;
    }

    /**
     * @return string|null
     */
    public function getContentType(): ?string {
        return $this->contentType;
    }

    /**
     * @return string|null
     */
    public function getAction(): ?string {
        return $this->action;
    }

    /**
     * Returns the key which was used for this permission.
     *
     * @return string|null
     */
    public function getKey(): ?string {
        return $this->contentType . '.' . $this->action;
    }

}