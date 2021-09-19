<?php

namespace Oxygen\Auth\Permissions;

/**
 * Instead of just returning "permitted" or "denied", we can provide more
 * information about where inherited permissions were derived from.
 */
class PermissionsExplanation {

    private $value;
    private ?PermissionsSource $source = null;
    private ?string $contentType = null;
    private ?string $action = null;
    private ?PermissionsSource $originalSource;
    private ?string $originalContentType;
    private ?string $originalAction;

    /**
     * @param bool|string $value
     * @param PermissionsSource|null $source if the permissions value came from a particular source
     * @param string|null $contentType
     * @param string|null $action
     */
    public function __construct($value) {
        $this->value = $value;
    }

    public function setRequestedInfo(PermissionsSource $originalSource, string $originalContentType, string $originalAction) {
        $this->originalSource = $originalSource;
        $this->originalContentType = $originalContentType;
        $this->originalAction = $originalAction;
    }

    /**
     * @param PermissionsSource $source
     * @param string $contentType
     * @param string $action
     */
    public function setSource(PermissionsSource $source, string $contentType, string $action) {
        $this->source = $source;
        $this->contentType = $contentType;
        $this->action = $action;
    }

    /**
     * @return bool|string
     */
    public function getValue() {
        return $this->value;
    }

    /**
     * @return null|PermissionsSource
     */
    public function getSource(): ?PermissionsSource {
        return $this->source;
    }

    /**
     * @return bool
     */
    public function hasSource(): bool {
        return $this->source !== null;
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

    /**
     * Returns the key which was used for this permission.
     *
     * @return string|null
     */
    public function getRequestedKey(): string {
        return $this->originalContentType . '.' . $this->originalAction;
    }

    /**
     * Converts to a Symfony console compatible explanation string.
     * @return string
     */
    public function toConsoleString(): string {
        $value = '';
        $val = $this->getValue();

        if(is_bool($val)) {
            if($val) {
                $value .= ($this->isSourceOriginal() && $this->getKey() === $this->getRequestedKey()) ? '<fg=black;bg=green;options=bold>Yes</>' : 'Yes';
            } else {
                $value .= ($this->hasSource()) ? '<error>No</error>' : 'No';
            }
        } else {
            $value .= $val;
        }

        if(!$this->hasSource() && is_bool($val)) {
            return '-';
        } else if(!$this->hasSource()) {
            return $value . ', by <fg=yellow;options=bold>default</>';
        }

        if($this->isSourceOriginal() && $this->getKey() !== $this->getRequestedKey()) {
            $value .= ', from <fg=cyan;options=bold>' . $this->getKey() . '</>';
        } else if(!$this->isSourceOriginal() && $this->getKey() === $this->getRequestedKey()) {
            $value .= ', from <fg=magenta;options=bold>' . $this->getSource()->getNickname() . '(...)</>';
        } else if(!$this->isSourceOriginal() && $this->getKey() !== $this->getRequestedKey()) {
            $value .= ', from <fg=magenta;options=bold>' . $this->getSource()->getNickname() . '(' . $this->getKey() . ')</>';
        }
        return $value;
    }

    /**
     * @return PermissionsSource|null
     */
    public function getOriginalSource(): ?PermissionsSource {
        return $this->originalSource;
    }

    /**
     * @return bool true if source was same as original source
     */
    public function isSourceOriginal(): bool {
        return $this->source === $this->originalSource;
    }

    /**
     * @param PermissionsExplanation $other
     * @return bool true if the two are equal
     */
    public function equals(PermissionsExplanation $other) {
        return $this->value === $other->getValue() && $this->getSource() === $other->getSource() && $this->getOriginalSource() === $other->getOriginalSource() && $this->getKey() === $other->getKey() && $this->getRequestedKey() === $other->getRequestedKey();
    }

}