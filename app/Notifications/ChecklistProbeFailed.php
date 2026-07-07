<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * A previously-passing checklist probe regressed (guides/checklists.md) —
 * sent to developer-tier users by ChecklistRunner.
 */
class ChecklistProbeFailed extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * @param  array<string, string>  $regressions  key => failure detail
     */
    public function __construct(public array $regressions) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $mail = (new MailMessage)
            ->subject(__(':app — checklist probe regression', ['app' => config('app.name')]))
            ->error()
            ->line(__('Checks that were passing are now failing:'));

        foreach ($this->regressions as $key => $detail) {
            $mail->line("• {$key}: {$detail}");
        }

        return $mail->action(__('Open checklists'), url('/admin/checklists'));
    }
}
