<?php

return [
    'report' => [
        'enabled' => env('IS_TELESCOPE_REPORT_ENABLED', false),
        'frequency' => env('TELESCOPE_REPORT_FREQUENCY', 7),
        'time' => env('TELESCOPE_REPORT_TIME_HOUR', 12),
        'driver' => env('TELESCOPE_REPORT_DRIVER', 'mail'),
        'mail_to' => env('TELESCOPE_REPORT_MAIL_TO'),
    ],
];
