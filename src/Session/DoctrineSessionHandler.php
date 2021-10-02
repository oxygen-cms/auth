<?php

namespace Oxygen\Auth\Session;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\Container\Container;
use Illuminate\Session\ExistenceAwareInterface;
use Illuminate\Support\Carbon;
use Illuminate\Support\InteractsWithTime;
use Oxygen\Auth\Entity\DoctrineSession;
use DateTime;
use Oxygen\Auth\Entity\User;
use SessionHandlerInterface;

class DoctrineSessionHandler implements ExistenceAwareInterface, SessionHandlerInterface {

    use InteractsWithTime;

    /**
     * The number of minutes the session should be valid.
     *
     * @var int
     */
    protected $minutes;

    /**
     * The container instance.
     *
     * @var Container|null
     */
    protected ?Container $container;

    /**
     * The existence state of the session.
     *
     * @var bool
     */
    protected $exists;

    /**
     * @var EntityManagerInterface|null
     */
    private ?EntityManagerInterface $em;

    /**
     * Create a new database session handler instance.
     *
     * @param int $minutes
     * @param Container|null $container
     */
    public function __construct(int $minutes, Container $container = null) {
        $this->minutes = $minutes;
        $this->em = null;
        $this->container = $container;
    }

    /**
     * {@inheritdoc}
     */
    public function open($savePath, $sessionName) {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function close() {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function read($sessionId) {
        $session = $this->getEntityManager()->find(DoctrineSession::class, $sessionId);

        if($session === null) {
            $this->exists = false;
            return '';
        }

        $this->exists = true;
        return !$session->expired($this->minutes) && $session->hasPayload() ?
            $session->getPayload() :
            '';
    }

    /**
     * {@inheritdoc}
     */
    public function write($sessionId, $data) {
        $updateAuxiliaryInfo = function(DoctrineSession $session) {
            $session->setLastActivity(Carbon::now()->toDateTime());
            $this->addUserInformation($session)
                ->addRequestInformation($session);
        };

        if(!$this->exists) {
            $this->read($sessionId);
        }

        $this->performUpdate($sessionId, $data, $updateAuxiliaryInfo);

        return $this->exists = true;
    }

    /**
     * Perform an update operation on the session ID.
     *
     * @param string $sessionId
     * @param string $data
     * @param callable $updateAuxiliaryInfo
     * @throws BindingResolutionException
     */
    protected function performUpdate($sessionId, string $data, callable $updateAuxiliaryInfo) {
        $session = $this->getEntityManager()->find(DoctrineSession::class, $sessionId);
        if($session === null) {
            $session = new DoctrineSession($sessionId, $data);
        }

        $updateAuxiliaryInfo($session);
        $this->getEntityManager()->persist($session);
        $this->getEntityManager()->flush();
    }

    /**
     * Add the user information to the session payload.
     *
     * @param DoctrineSession $session
     * @return $this
     * @throws BindingResolutionException
     */
    protected function addUserInformation(DoctrineSession $session) {
        if($this->container && $this->container->bound(Guard::class)) {
            $user = $this->container->make(Guard::class)->user();
            if($user !== null && !$this->getEntityManager()->contains($user)) {
                // if we are trying to update the user model and it fails, then the EntityManager will get closed
                // then the `user` instance is managed by the old entity manager, which will cause issues when
                // we then try to persist the DoctrineSession entity.
                //
                // in order to fix this, we need to retrieve a fresh User entity from scratch
                $user = $this->getEntityManager()->find(User::class, $user->getId());
            }
            $session->setUser($user);
        }

        return $this;
    }

    /**
     * Add the request information to the session payload.
     *
     * @param DoctrineSession $session
     * @return $this
     * @throws BindingResolutionException
     */
    protected function addRequestInformation(DoctrineSession $session) {
        if($this->container && $this->container->bound('request')) {
            $session->setIpAddress($this->ipAddress());
            $session->setUserAgent($this->userAgent());
        }

        return $this;
    }

    /**
     * Get the IP address for the current request.
     *
     * @return string
     * @throws BindingResolutionException
     */
    protected function ipAddress() {
        return $this->container->make('request')->ip();
    }

    /**
     * Get the user agent for the current request.
     *
     * @return string
     * @throws BindingResolutionException
     */
    protected function userAgent() {
        return substr((string) $this->container->make('request')->header('User-Agent'), 0, 500);
    }

    /**
     * {@inheritdoc}
     */
    public function destroy($sessionId) {
        $this->getQuery()->delete(DoctrineSession::class, 'u')
            ->where('u.id = :sessionId')
            ->setParameter('sessionId', $sessionId)
            ->getQuery()
            ->execute();

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function gc($lifetime) {
        $this->getQuery()
            ->delete(DoctrineSession::class, 'u')
            ->where('u.lastActivity <= :staleTime')
            ->setParameter('staleTime', $this->getStaleTime($lifetime))
            ->getQuery()
            ->execute();
        return true;
    }

    /**
     * Get the current system time as a UNIX timestamp.
     *
     * @return DateTime
     */
    protected function getStaleTime($lifetime) {
        return Carbon::now()->subSeconds($lifetime)->toDateTime();
    }

    /**
     * Get a fresh query builder instance for the table.
     *
     * @return QueryBuilder
     * @throws BindingResolutionException
     */
    protected function getQuery() {
        return $this->getEntityManager()->createQueryBuilder();
    }

    /**
     * Set the existence state for the session.
     *
     * @param  bool  $value
     * @return $this
     */
    public function setExists($value) {
        $this->exists = $value;

        return $this;
    }

    /**
     * Returns a list of sessions for a user.
     *
     * @param User $user
     * @return int|mixed|string
     * @throws BindingResolutionException
     */
    public function getSessionsForUser(User $user) {
        return $this->getQuery()->select('o')
            ->from(DoctrineSession::class, 'o')
            ->where('o.user = :user')
            ->setParameter('user', $user)
            ->getQuery()
            ->getResult();
    }

    /**
     * Returns an entity manager, possibly resetting it if it was previously closed.
     *
     * @return EntityManagerInterface
     * @throws BindingResolutionException
     */
    private function getEntityManager(): EntityManagerInterface {
        if($this->em !== null && $this->em->isOpen()) {
            return $this->em;
        }
        $registry = $this->container->make('registry');
        $this->em = $registry->getManager();
        if(!$this->em->isOpen()) {
            // try to reset the connection... we want to save session data at all costs
            $this->em = $registry->resetManager();
        }
        return $this->em;
    }
}