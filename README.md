# Laravel Telescope Extension

## Installation

Install the package using the following command

```sh
composer require ronasit/laravel-telescope-extension
```

That's it!

## Usage

Manually call the console command `telescope:prune` with your options. For example:

```sh
php artisan telescope:prune --set-hours=request:240,query:24,unresolved-exception:480 --hours=100
```

The explanation: remove all records with entity type `request` older than 240 hours,
with entity type `query` older than 24 hours,
and entity type `unresolved-exception` older than 480 hours.
Also, remove records of all other entity types older than 100 hours.

Command's options have the following formats:

| Option      | Format                                         | Description                              |
|-------------|------------------------------------------------|------------------------------------------|
| --set-hours | [entity-type]:[hours],[entity-type]:[hours]... | List of rules for specified entity types |
| --hours     | [hours]                                        | Rule for all other entity types          |

Here is the list of possible `entity-type` values:

| Entity Type          |
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