<?php

namespace RonasIT\TelescopeExtension\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

class ReportMail extends Mailable implements ShouldQueue
{
    use Queueable;
    use SerializesModels;

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
        public string $telescopeBaseUrl,
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: config('app.name') . ' telescope report',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'telescope::emails.report',
            with: [
                'entryEmojiMap' => self::ENTRY_EMOJI_MAP,
                'entryDisplayNameMap' => self::ENTRY_DISPLAY_NAME_MAP,
            ],
        );
    }
}
