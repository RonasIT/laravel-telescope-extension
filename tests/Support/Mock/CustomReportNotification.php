<?php

namespace RonasIT\TelescopeExtension\Tests\Support\Mock;

use Illuminate\Notifications\Notification;
use Illuminate\Support\Collection;
use RonasIT\TelescopeExtension\Contracts\ReportNotificationContract;

class CustomReportNotification extends Notification implements ReportNotificationContract
{
    public function __construct(
        public Collection $entries,
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }
}
