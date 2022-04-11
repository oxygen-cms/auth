<?php

namespace Oxygen\Auth\Entity;

use Illuminate\Contracts\Auth\Authenticatable;

trait Blames {

    /**
     * @ORM\ManyToOne(fetch="LAZY")
     */
    private ?User $createdBy;

    /**
     * @ORM\ManyToOne(fetch="LAZY")
     */
    private ?User $updatedBy;

    /**
     * @return User|null
     */
    public function getCreatedBy(): ?User {
        return $this->createdBy;
    }

    /**
     * @return User|null
     */
    public function getUpdatedBy(): ?User {
        return $this->updatedBy;
    }

    /**
     * @param Authenticatable $createdBy
     */
    public function setCreatedBy(Authenticatable $createdBy): void {
        $this->createdBy = $createdBy;
    }

    /**
     * @param Authenticatable $updatedBy
     */
    public function setUpdatedBy(Authenticatable $updatedBy): void {
        $this->updatedBy = $updatedBy;
    }

}