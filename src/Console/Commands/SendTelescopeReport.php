<?php

namespace RonasIT\TelescopeExtension\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use RonasIT\TelescopeExtension\Notifications\ReportNotification;

class SendTelescopeReport extends Command
{
    protected $signature = 'telescope-report:send';

    protected $description = 'Command description';

    public function handle(): void
    {
        $entries = $this->getEntryCounts();

        Notification::route(
            channel: config('notifications.report.driver'),
            route: config('notifications.report.mail_to'),
        )->notify(new ReportNotification($entries));
    }

    private function getEntryCounts(): array
    {
        return DB::table('telescope_entries')
            ->select(DB::raw('type, count(*) as count'))
            ->groupBy('type')
            ->pluck('count', 'type')
            ->toArray();
    }
}
