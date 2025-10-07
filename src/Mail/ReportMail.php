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
                'entryEmojiMap' => config('telescope.notifications.report.entry_emoji_map'),
                'entryDisplayNameMap' => config('telescope.notifications.report.entry_display_name_map'),
            ],
        );
    }
}
