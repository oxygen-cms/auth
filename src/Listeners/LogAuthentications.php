<?php


namespace Oxygen\Auth\Listeners;

use Doctrine\ORM\EntityManager;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Logout;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Auth\Events\Login;
use Oxygen\Auth\Entity\AuthenticationLogEntry;
use Oxygen\Auth\Notifications\NewDeviceNotification;
use Oxygen\Auth\Repository\AuthenticationLogEntryRepositoryInterface;
use Oxygen\Preferences\PreferencesManager;

class LogAuthentications {
    /**
     * The request.
     *
     * @var \Illuminate\Http\Request
     */
    public $request;

    /**
     * @var AuthenticationLogEntryRepositoryInterface
     */
    private $log;
    /**
     * @var PreferencesManager
     */
    private $preferences;

    /**
     * Create the event listener.
     *
     * @param \Illuminate\Http\Request $request
     * @param AuthenticationLogEntryRepositoryInterface $log
     */
    public function __construct(Request $request, AuthenticationLogEntryRepositoryInterface $log, PreferencesManager $preferences) {
        $this->request = $request;
        $this->log = $log;
        $this->preferences = $preferences;
    }

    /**
     * Handle the event.
     *
     * @param mixed $event
     * @return void
     */
    public function handle($event) {
        $user = $event->user;
        $ip = $this->request->ip();
        $userAgent = $this->request->userAgent();


        $authenticationLog = new AuthenticationLogEntry();
        $authenticationLog->setIpAddress($ip);
        $authenticationLog->setUserAgent($userAgent);
        if($user !== null) {
            $authenticationLog->setUser($user);
        }

        if($event instanceof Login) {
            $authenticationLog->setType(AuthenticationLogEntry::LOGIN_SUCCESS);

            $known = $this->log->isKnownDevice($user, $ip, $userAgent);

            if(!$known && !$this->log->isFirstLogin($user) && $this->preferences->get('modules.auth::notifyWhenNewDevice', true)) {
                $user->notify(new NewDeviceNotification($authenticationLog));
            }
        } else if($event instanceof Logout) {
            $authenticationLog->setType(AuthenticationLogEntry::LOGOUT);
        } else if($event instanceof Failed) {
            $authenticationLog->setUsername($event->credentials['username']);
            $authenticationLog->setType(AuthenticationLogEntry::LOGIN_FAILED);
        }

        $this->log->persist($authenticationLog);
        $this->log->flush();
    }
}
