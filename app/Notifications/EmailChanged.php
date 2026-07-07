<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Sent to the OLD address when an account's email is changed, so the rightful
 * owner learns about a hijack even after losing the session.
 */
class EmailChanged extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public string $oldEmail,
        public string $newEmail,
    ) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject(__('Your account email address was changed'))
            ->line(__('The email address on your :app account was changed from :old to :new.', [
                'app' => config('app.name'),
                'old' => $this->oldEmail,
                'new' => $this->newEmail,
            ]))
            ->line(__('If you made this change, no action is needed.'))
            ->line(__('If you did NOT make this change, contact support immediately — your account may be compromised.'));
    }
}
