<?php

namespace Oxygen\Auth\Entity;

use Doctrine\ORM\Mapping AS ORM;

/**
 * @ORM\Entity
 */
class Notification extends \LaravelDoctrine\ORM\Notifications\Notification {
    /**
     * @ORM\ManyToOne(targetEntity="Oxygen\Auth\Entity\User")
     */
    protected $user;
}