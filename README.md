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

### Updated prune command

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

### Store content in JSON field

The content field in the `telescope_entries` table now has the `jsonb` type which makes it easier to work with using the database management system's tools.

### Production Filter

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

### Request Watcher

The extended Request watcher works with the new configuration and allows skipping incoming HTTP requests based on the `message` field in the response:

```php
        RequestWatcher::class => [
            .....
            'ignore_error_messages' => [],
        ],
```
