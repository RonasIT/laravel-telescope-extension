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

    protected const array ENTRY_EMOJI_MAP = [
        'cache' => '📦',
        'client-requests'=> '📡',
        'requests' => '🌐',
        'commands' => '⌨️',
        'queries' => '📊',
        'mail' => '✉️',
        'views' => '🖥',
        'redis' => '⚡️',
        'exceptions' => '⚠️',
        'notifications' => '🔔',
        'jobs' => '💥',
        'schedule' => '🕒',
        'batches' => '🗂',
        'logs' => '📑',
        'gates' => '🚪',
        'events' => '🎫',
        'models' => '🤖',
        'dumps' => '📝',
    ];

    protected const array ENTRY_DISPLAY_NAME_MAP = [
        'exceptions' => 'Unresolved exceptions',
        'jobs' => 'Failed jobs',
    ];

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
        return (new ReportMail(
            entries: $this->entries,
            telescopeBaseUrl: URL::to(config('telescope.path')),
            entryEmojiMap: self::ENTRY_EMOJI_MAP,
            entryDisplayNameMap: self::ENTRY_DISPLAY_NAME_MAP,
        ))->to(config('telescope.notifications.report.drivers.mail.to'));
    }
}
