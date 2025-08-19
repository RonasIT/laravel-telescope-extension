<?php

namespace RonasIT\TelescopeExtension\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Notification;
use Laravel\Telescope\EntryType;
use RonasIT\TelescopeExtension\Notifications\ReportNotification;
use RonasIT\TelescopeExtension\Repositories\TelescopeRepository;

class SendTelescopeReport extends Command
{
    protected const array entryTypeMap = [
        EntryType::CACHE => 'cache',
        EntryType::CLIENT_REQUEST => 'client-requests',
        EntryType::COMMAND => 'commands',
        EntryType::EXCEPTION => 'exceptions',
        EntryType::JOB => 'jobs',
        EntryType::MAIL => 'mail',
        EntryType::MODEL => 'models',
        EntryType::QUERY => 'queries',
        EntryType::REDIS => 'redis',
        EntryType::REQUEST => 'requests',
        EntryType::SCHEDULED_TASK => 'schedule',
        EntryType::VIEW => 'views',
        EntryType::BATCH => 'batches',
        EntryType::DUMP => 'dumps',
        EntryType::EVENT => 'events',
        EntryType::GATE => 'gates',
        EntryType::LOG => 'logs',
        EntryType::NOTIFICATION => 'notifications',
    ];

    protected $signature = 'telescope-report:send';

    protected $description = 'Send telescope report';

    public function handle(): void
    {
        $entries = app(TelescopeRepository::class)->getEntryCounts();
        $entries = $entries->mapWithKeys(fn ($count, $entry) => [self::entryTypeMap[$entry] => $count]);

        Notification::route(
            channel: config('notifications.report.driver'),
            route: config('notifications.report.mail_to'),
        )->notify(new ReportNotification($entries));
    }
}
