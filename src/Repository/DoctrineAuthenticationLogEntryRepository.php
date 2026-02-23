<?php

namespace Oxygen\Auth\Repository;

use Doctrine\ORM\EntityManagerInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Oxygen\Auth\Entity\AuthenticationLogEntry;
use Oxygen\Auth\Entity\User;
use Oxygen\Data\Pagination\PaginationService;
use Oxygen\Data\Repository\Doctrine\Repository;
use Oxygen\Core\Preferences\PreferencesManager;

class DoctrineAuthenticationLogEntryRepository extends Repository implements AuthenticationLogEntryRepositoryInterface {

    /**
     * The name of the entity.
     *
     * @var string
     */
    protected $entityName = AuthenticationLogEntry::class;

    /**
     * @var PreferencesManager
     */
    private $preferences;

    /**
     * Constructs the DoctrineRepository.
     *
     * @param EntityManagerInterface   $entities
     * @param PaginationService        $paginator
     */
    public function __construct(EntityManagerInterface $entities, PaginationService $paginator, PreferencesManager $preferences) {
        parent::__construct($entities, $paginator);
        $this->preferences = $preferences;
    }

    /**
     * Returns true if this is a known device for this user.
     *
     * @param User $user
     * @param string $ipAddress
     * @param string $userAgent
     * @return bool
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function isKnownDevice(User $user, string $ipAddress, string $userAgent) {
        return intval($this->createCountQuery()
            ->where('o.user = :user')
            ->andWhere('o.ipAddress = :ipAddress')
            ->andWhere('o.userAgent = :userAgent')
            ->andWhere('o.type = :ty')
            ->setParameter('user', $user)
            ->setParameter('ipAddress', $ipAddress)
            ->setParameter('ty', AuthenticationLogEntry::LOGIN_SUCCESS)
            ->setParameter('userAgent', $userAgent)->getQuery()->getSingleScalarResult()) > 0;
    }

    /**
     * Returns true if the user has never logged in before.
     * 
     * @param User $user
     * @return bool
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function isFirstLogin(User $user) {
        $numLogins = intval($this->createCountQuery()
                ->where('o.user = :user')
                ->andWhere('o.type = :ty')
                ->setParameter('user', $user)
                ->setParameter('ty', AuthenticationLogEntry::LOGIN_SUCCESS)
                ->getQuery()->getSingleScalarResult());
        return $numLogins === 0;
    }

    /**
     * @param User $user
     * @param integer $perPage
     * @param integer|null $currentPage
     * @return LengthAwarePaginator
     * @throws \Exception
     */
    public function findByUser(User $user, $perPage, $currentPage = null) {
        $q = $this->createSelectQuery()
            ->where('o.user = :user')
            ->setParameter('user', $user)
            ->orderBy('o.id', 'DESC')
            ->getQuery();
        return $this->applyPagination($q, $perPage, $currentPage);
    }

    /**
     * Also deletes older records
     */
    public function flush() {
        try {
            $days = $this->preferences->get('modules.auth::loginLogExpiry', '30');
            $expiryDate = (new \DateTime())
                ->sub(new \DateInterval('P' . $days . 'D'));

            $this->entities
                ->createQueryBuilder()
                ->delete($this->entityName, 'o')
                ->where('o.timestamp <= :timestamp')
                ->setParameter('timestamp', $expiryDate)
                ->getQuery()
                ->execute();
        } catch(\Exception $e) {
            report($e);
        }
        $this->entities->flush();
    }
}