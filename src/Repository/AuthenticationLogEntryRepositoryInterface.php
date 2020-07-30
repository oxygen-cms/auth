<?php

namespace Oxygen\Auth\Repository;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Oxygen\Auth\Entity\User;
use Oxygen\Data\Repository\RepositoryInterface;

interface AuthenticationLogEntryRepositoryInterface extends RepositoryInterface {

    /**
     * Returns true if this is a known device for this user.
     *
     * @param User $user
     * @param string $ipAddress
     * @param string $userAgent
     */
    public function isKnownDevice(User $user, string $ipAddress, string $userAgent);

    /**
     * Returns true if the user has never logged in before.
     *
     * @param User $user
     * @return bool
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function isFirstLogin(User $user);

    /**
     * @param User $user
     * @param integer $perPage
     * @param integer|null $currentPage
     * @return LengthAwarePaginator
     */
    public function findByUser(User $user, $perPage, $currentPage = null);
}