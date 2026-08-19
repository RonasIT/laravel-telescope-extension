# Laravel Telescope Extension

[![Coverage Status](https://coveralls.io/repos/github/RonasIT/laravel-telescope-extension/badge.svg?branch=main)](https://coveralls.io/github/RonasIT/laravel-telescope-extension?branch=main)

The library extends the [Laravel Telescope's](https://github.com/laravel/telescope) package. 

## Installation

Install the package using the following command

```sh
composer require ronasit/laravel-telescope-extension
```

Publish the package configuration:

``` sh
php artisan vendor:publish --provider=RonasIT\\TelescopeExtension\\TelescopeExtensionServiceProvider
```

That's it!

## Features

### 🔒 Restricting Access by IP in the Production environment

Package allows to restrict access to the Telescope only in the `production` environment using an IP whitelist.

Modify the `allowed_ips` config for this.

Empty config value will allow to have access from **any** IP address.

### 🧹 Updated prune command

Manually call the console command `telescope:prune` with your options
or specify it into [schedule](https://laravel.com/docs/12.x/scheduling#scheduling-artisan-commands). For example:

```sh
php artisan telescope:prune --set-hours=request:240,query:24,unresolved_exception:480 --hours=100
```

The explanation: remove all records with entry type `request` older than 240 hours,
with entry type `query` older than 24 hours,
and entry type `unresolved-exception` older than 480 hours.
Also, remove records of all other entry types older than 100 hours.

Command's options have the following formats:

| Option      | Format                                       | Description                             |
|-------------|----------------------------------------------|-----------------------------------------|
| --set-hours | [entry-type]:[hours],[entry-type]:[hours]... | List of rules for specified entry types |
| --hours     | [hours]                                      | Rule for all other entry types          |

Here is the list of possible `entry-type` values:

| Entry Type           |
|----------------------|
| batch                |
| cache                |
| dump                 |
| event                |
| exception            |
| job                  |
| log                  |
| mail                 |
| model                |
| notification         |
| query                |
| redis                |
| request              |
| schedule             |
| gate                 |
| view                 |
| unresolved_exception |
| resolved_exception   |
| completed_job        |

### 🧩 Store content in JSON field

The content field in the `telescope_entries` table now has the `jsonb` type which makes it easier to work with using the database management system's tools.

### 🔍 Production Filter

Feel free to use the predefined telescope filter for the `production` environment. It'll collect next entries:

• Exceptions

• Incoming HTTP requests with the status >= `400`

• Outgoing HTTP requests with the status >= `400`

• Failed jobs

• Slow DB queries

• Scheduled tasks

To enable the filter just use it in your own `TelescopeServiceProvider`

```php
Telescope::filter(call_user_func(new \RonasIT\TelescopeExtension\Filters\ProductionFilter));
```

### 👀 Extended Request Watcher

The extended Request watcher provides new configurable logic.

#### 🙈 Ignoring requests by response message

Watcher will ignore incoming HTTP requests if the `message` field in the response is equal to one of the ignorable messages.

Just add the full message to the `ignore_error_messages` config.

#### 🙈 Ignoring requests by path

Works the same as the `ignore` config of the CommandWatcher. The watcher will skip incoming HTTP requests if they are made to one of the ignorable paths.

Use the `ignore_paths` config for this.

The main difference between this config and the global telescope `ignore_paths` config is that the request watcher's config will ignore only incoming HTTP
requests and will still store all other entries related to the request (such as queries, jobs, exceptions, etc.)

### 📬 Periodic report

The package can send a periodic report with the number of entries collected by Telescope, grouped by entry type. Every
entry type in the report is a link to the corresponding Telescope tab.

The report is sent by the `telescope:send-report` command. There is no need to register it in your scheduler: the
package schedules the command automatically as soon as the report is enabled. You can also send the report manually at
any time:

```sh
php artisan telescope:send-report
```

#### Configuration

All the settings are placed in the `notifications.report` section of the `telescope` config:

| Config key               | Env variable                  | Default    | Description                                   |
|--------------------------|-------------------------------|------------|-----------------------------------------------|
| `enabled`                | `IS_TELESCOPE_REPORT_ENABLED` | `false`    | Enables the scheduled report                  |
| `frequency`              | `TELESCOPE_REPORT_FREQUENCY`  | `7`        | Sending frequency in days                     |
| `time`                   | `TELESCOPE_REPORT_TIME_HOUR`  | `12`       | Hour of the day when the report is sent       |
| `driver`                 | `TELESCOPE_REPORT_DRIVER`     | `mail`     | Notification channel or a list of channels    |
| `drivers.mail.to`        | `TELESCOPE_REPORT_MAIL_TO`    | `''`       | Comma-separated list of recipients            |
| `entry_emoji_map`        | —                             | see config | Emoji displayed next to each entry type       |
| `entry_display_name_map` | —                             | see config | Overrides the displayed name of an entry type |

The command is scheduled daily at `{time}:00` and sends the report only on the days when the day of the year is
divisible by `frequency`, so with the default value of `7` the report is sent about once a week.

#### Report content

Entries are counted per type with the following exceptions:

• Exceptions — only unresolved ones are counted
• Jobs — only failed ones are counted
• Entries having a `family_hash` are counted by unique hash, so the same repeated entry is counted once

#### Customizing the report template

Publish the mail template and modify it:

``` sh
php artisan vendor:publish --provider=RonasIT\\TelescopeExtension\\TelescopeExtensionServiceProvider --tag=view
```

The template will be published to `resources/views/vendor/telescope/emails/report.blade.php`.

#### Extending the report notification

By default, the report is sent via `RonasIT\TelescopeExtension\Notifications\ReportNotification`, which supports the
`mail` channel only. To deliver the report to other channels, bind your own notification class to the
`ReportNotificationContract` in `app/Providers/AppServiceProvider.php`:

```php
use RonasIT\TelescopeExtension\Contracts\ReportNotificationContract;
use App\Notifications\CustomReportNotification;

public function register(): void
{
    $this->app->bind(ReportNotificationContract::class, CustomReportNotification::class);
}
```

The easiest way is to extend the default `ReportNotification`: it already implements `ReportNotificationContract`,
accepts the `$entries` collection and provides the `mail` channel implementation, so only the additional channels have to
be declared. For example, to send the report both by mail and to Telegram:

```php
<?php

namespace App\Notifications;

use NotificationChannels\Telegram\TelegramMessage;
use RonasIT\TelescopeExtension\Notifications\ReportNotification;

class CustomReportNotification extends ReportNotification
{
    public function via(object $notifiable): array
    {
        return array_merge(parent::via($notifiable), [
            'telegram',
        ]);
    }

    public function toTelegram(object $notifiable): TelegramMessage
    {
        // your telegram implementation
    }
}
```

If you prefer to implement the notification from scratch, it must extend `Illuminate\Notifications\Notification`,
implement `ReportNotificationContract` and accept a constructor parameter named exactly `$entries` of type `Collection`
— the package resolves the notification via `makeWith(['entries' => $entries])`, which matches constructor parameters by
name. The contract requires a `via(object $notifiable): array` method, so the list of channels always stays under your
control.
