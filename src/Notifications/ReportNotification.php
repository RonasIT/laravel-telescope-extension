<?php

namespace RonasIT\TelescopeExtension\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ReportNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public array $entries,
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage())
            ->subject('Laravel telescope report')
            ->view('emails.report', [
                'entries' => $this->entries,
                'telescopeBaseUrl' => config('app.url') . '/' . config('telescope.path'),
            ]);
    }
}
