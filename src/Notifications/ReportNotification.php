<?php

namespace RonasIT\TelescopeExtension\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Collection;

class ReportNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Collection $entries,
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage())
            ->subject(config('app.name') . ' telescope report')
            ->view('telescope::emails.report', [
                'entries' => $this->entries,
                'telescopeBaseUrl' => config('app.url') . '/' . config('telescope.path'),
            ]);
    }
}
