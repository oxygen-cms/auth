<?php


namespace Oxygen\Auth\Notifications;

use Carbon\Carbon;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use Oxygen\Auth\Entity\AuthenticationLogEntry;

class NewDeviceNotification extends Notification {

    /**
     * The authentication log.
     *
     * @var AuthenticationLogEntry
     */
    public $authenticationLog;

    /**
     * Create a new notification instance.
     *
     * @param AuthenticationLogEntry $authenticationLog
     * @return void
     */
    public function __construct(AuthenticationLogEntry $authenticationLog) {
        $this->authenticationLog = $authenticationLog;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param mixed $notifiable
     * @return array
     */
    public function via($notifiable) {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param mixed $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable) {
        return (new MailMessage)
            ->subject(trans('oxygen/auth::messages.newDeviceNotification.subject'))
            ->markdown('oxygen/auth::newDeviceNotification', [
                'account' => $notifiable,
                'time' => new Carbon($this->authenticationLog->getTimestamp()),
                'ipAddress' => $this->authenticationLog->getIpAddress(),
                'browser' => $this->authenticationLog->getUserAgent(),
            ]);
    }

}
