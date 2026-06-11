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

### 📬 Customizing Report Notifications

By default, the package sends reports via `RonasIT\TelescopeExtension\Notifications\ReportNotification`. You can replace it with your own notification class by binding your implementation to the `ReportNotificationContract` in service provider.

#### Overriding the default notification

In `app/Providers/AppServiceProvider.php`:

```php
use RonasIT\TelescopeExtension\Contracts\ReportNotificationContract;
use App\Notifications\CustomReportNotification;

public function register(): void
{
    $this->app->bind(ReportNotificationContract::class, CustomReportNotification::class);
}
```

Your custom notification must implement `ReportNotificationContract` and accept `Collection $entries` in its constructor.

#### Example: adding a custom report notification

```php
<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Collection;
use NotificationChannels\Telegram\TelegramMessage;
use RonasIT\TelescopeExtension\Contracts\ReportNotificationContract;

class CustomReportNotification extends Notification implements ShouldQueue, ReportNotificationContract
{
    use Queueable;

    public function __construct(
        public Collection $entries, 
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['mail', 'telegram'];
    }

    public function toMail(object $notifiable): \Illuminate\Mail\Mailable
    {
        // your mail implementation
    }

    public function toTelegram(object $notifiable): TelegramMessage
    {
        // your telegram implementation
    }
}
```

If no custom binding is provided, the package falls back to the default `ReportNotification` behavior.
