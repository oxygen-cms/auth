<?php

namespace Oxygen\Auth\Controller;

use Doctrine\ORM\EntityManager;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Oxygen\Auth\Entity\AuthenticationLogEntry;
use Oxygen\Auth\Entity\DoctrineSession;
use Oxygen\Auth\Entity\User;
use Oxygen\Auth\Repository\AuthenticationLogEntryRepositoryInterface;
use Oxygen\Auth\Session\DoctrineSessionHandler;
use Oxygen\Core\Http\Notification;
use Webmozart\Assert\Assert;

class AuthenticationLogController extends Controller {

    const AUTHENTICATION_LOG_PER_PAGE = 10;

    /**
     * Get entries from the login log.
     *
     * @param AuthenticationLogEntryRepositoryInterface $entries
     * @return JsonResponse
     */
    public function getAuthenticationLogEntries(AuthenticationLogEntryRepositoryInterface $entries) {
        $user = auth()->user();
        Assert::isInstanceOf($user, User::class);
        $paginator = $entries->findByUser($user, self::AUTHENTICATION_LOG_PER_PAGE);

        return response()->json([
            'items' => array_map(function(AuthenticationLogEntry $e) { return $e->toArray(); }, $paginator->items()),
            'totalItems' => $paginator->total(),
            'itemsPerPage' => $paginator->perPage(),
            'status' => Notification::SUCCESS
        ]);
    }

    /**
     * Returns filled in IP geolocation data from a geolocation service.
     * @param string $ip
     * @return Application|ResponseFactory|JsonResponse|Response
     * @throws GuzzleException
     */
    public function getIPGeolocation(string $ip) {
        $client = new Client();

        try {
            $res = $client->request('GET', config('oxygen.auth.ipGeolocationUrl'), [
                'query' => ['apiKey' => config('oxygen.auth.ipGeolocationKey'), 'ip' => $ip]
            ]);
            return response($res->getBody());
        } catch(ClientException $e) {
            report($e);
            return response()->json([
                'content' => 'IP geolocation failed',
                'status' => Notification::FAILED
            ]);
        }
    }

    /**
     * Returns a list of active sessions for the current user.
     *
     * @param DoctrineSessionHandler $doctrineSessionHandler
     * @param Request $request
     * @return JsonResponse
     */
    public function getUserSessions(DoctrineSessionHandler $doctrineSessionHandler, Request $request) {
        $sessions = $doctrineSessionHandler->getSessionsForUser($request->user());
        return response()->json([
            'sessions' => array_map(function(DoctrineSession $session) {
                return [
                    'id' => $session->getId(),
                    'current' => $session->getId() === session()->getId(),
                    'ipAddress' => $session->getIpAddress(),
                    'userAgent' => $session->getUserAgent(),
                    'lastActivity' => $session->getLastActivity()->format(\DateTimeInterface::ISO8601)
                ];
            }, $sessions)
        ]);
    }

    /**
     * Returns a list of active sessions for the current user.
     *
     * @param string $id the session id to deleete
     * @param EntityManager $entityManager
     * @param Request $request
     * @return JsonResponse
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\TransactionRequiredException
     */
    public function deleteUserSession($id, EntityManager $entityManager, Request $request) {
        $session = $entityManager->find(DoctrineSession::class, $id);
        if($session !== null && $session->getUser() === $request->user()) {
            $entityManager->remove($session);
            $entityManager->flush();
        }
        return response()->json([
            'code' => 'removed'
        ]);
    }

}