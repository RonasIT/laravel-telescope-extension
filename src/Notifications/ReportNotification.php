<?php

namespace RonasIT\TelescopeExtension\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\URL;
use RonasIT\TelescopeExtension\Mail\ReportMail;

class ReportNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Collection $entries,
    ) {
    }

    public function via(object $notifiable): array
    {
        return Arr::wrap(config('telescope.notifications.report.driver'));
    }

    public function toMail(object $notifiable): Mailable
    {
        $mailData = [
            'entries' => $this->entries,
            'telescopeBaseUrl' => URL::to(config('telescope.path')),
        ];

        return (new ReportMail($mailData))->to(config('telescope.notifications.report.drivers.mail.to'));
    }
}
