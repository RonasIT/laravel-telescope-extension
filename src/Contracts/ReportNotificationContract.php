<?php

namespace RonasIT\TelescopeExtension\Contracts;

interface ReportNotificationContract
{
    public function via(object $notifiable): array;
}
