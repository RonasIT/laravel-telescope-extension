<?php

namespace RonasIT\TelescopeExtension\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use RonasIT\TelescopeExtension\Mail\ReportMail;

class ReportNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected string $driver;

    public function __construct(
        public Collection $entries,
    ) {
        $this->driver = config('telescope.notifications.report.driver');
    }

    public function via(object $notifiable): array
    {
        return Arr::wrap($this->driver);
    }

    public function toMail(object $notifiable): Mailable
    {
        return (new ReportMail([
            'entries' => $this->entries,
            'telescopeBaseUrl' => config('app.url') . '/' . config('telescope.path'),
        ]))->to(config("telescope.notifications.report.drivers.{$this->driver}.to"));
    }
}
