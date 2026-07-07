<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Sent when an admin approves a pending account (auth.require_approval gate).
 */
class AccountApproved extends Notification implements ShouldQueue
{
    use Queueable;

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
            ->subject(__('Your account has been approved'))
            ->line(__('Good news — your :app account has been approved.', ['app' => config('app.name')]))
            ->action(__('Continue to :app', ['app' => config('app.name')]), route('dashboard'))
            ->line(__('If you have any questions, just reply to this email.'));
    }
}
