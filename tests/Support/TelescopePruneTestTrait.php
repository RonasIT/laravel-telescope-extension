<?php

namespace RonasIT\TelescopeExtension\Tests\Support;

use Illuminate\Support\Carbon;
use Laravel\Telescope\EntryType;

trait TelescopePruneTestTrait
{
    use SQLMockTrait;

    protected function mockQueriesWithoutParameters(): void
    {
        $this->mockDelete('delete from "telescope_entries"');

        $this->mockDelete('delete from "telescope_monitoring"');
    }

    protected function mockQueriesWithOnlyHours(): void
    {
        $this->mockDelete(
            'delete from "telescope_entries" where "rowid" in (select "telescope_entries"."rowid" '
            . 'from "telescope_entries" where "created_at" < ? limit 1000)',
            [Carbon::now()->subHours(2)->toDateTimeString()],
            1000
        );

        $this->mockDelete(
            'delete from "telescope_entries" where "rowid" in (select "telescope_entries"."rowid" '
            . 'from "telescope_entries" where "created_at" < ? limit 1000)',
            [Carbon::now()->subHours(2)->toDateTimeString()],
            123
        );

        $this->mockDelete(
            'delete from "telescope_entries" where "rowid" in (select "telescope_entries"."rowid" '
            . 'from "telescope_entries" where "created_at" < ? limit 1000)',
            [Carbon::now()->subHours(2)->toDateTimeString()]
        );
    }

    protected function mockQueriesWithSingleSetHours(): void
    {
        $this->mockDelete(
            'delete from "telescope_entries" where "rowid" in (select "telescope_entries"."rowid" '
            . 'from "telescope_entries" where "created_at" < ? and ("type" = ?) limit 1000)',
            [Carbon::now()->subHours(5)->toDateTimeString(), EntryType::REQUEST],
            200
        );

        $this->mockDelete(
            'delete from "telescope_entries" where "rowid" in (select "telescope_entries"."rowid" '
            . 'from "telescope_entries" where "created_at" < ? and ("type" = ?) limit 1000)',
            [Carbon::now()->subHours(5)->toDateTimeString(), EntryType::REQUEST]
        );
    }

    protected function mockQueriesWithSeveralSetHours(): void
    {
        $this->mockDelete(
            'delete from "telescope_entries" where "rowid" in (select "telescope_entries"."rowid" '
            . 'from "telescope_entries" where "created_at" < ? and ("type" = ?) limit 1000)',
            [Carbon::now()->subHours(5)->toDateTimeString(), EntryType::REQUEST],
            200
        );

        $this->mockDelete(
            'delete from "telescope_entries" where "rowid" in (select "telescope_entries"."rowid" '
            . 'from "telescope_entries" where "created_at" < ? and ("type" = ?) limit 1000)',
            [Carbon::now()->subHours(5)->toDateTimeString(), EntryType::REQUEST]
        );

        $this->mockDelete(
            'delete from "telescope_entries" where "rowid" in (select "telescope_entries"."rowid" '
            . 'from "telescope_entries" where "created_at" < ? and ("type" = ?) limit 1000)',
            [Carbon::now()->subHours(100)->toDateTimeString(), EntryType::REDIS],
            100
        );

        $this->mockDelete(
            'delete from "telescope_entries" where "rowid" in (select "telescope_entries"."rowid" '
            . 'from "telescope_entries" where "created_at" < ? and ("type" = ?) limit 1000)',
            [Carbon::now()->subHours(100)->toDateTimeString(), EntryType::REDIS]
        );

        $this->mockDelete(
            'delete from "telescope_entries" where "rowid" in (select "telescope_entries"."rowid" '
            . 'from "telescope_entries" where "created_at" < ? and ("type" = ?) limit 1000)',
            [Carbon::now()->subHours(25)->toDateTimeString(), EntryType::QUERY],
            50
        );

        $this->mockDelete(
            'delete from "telescope_entries" where "rowid" in (select "telescope_entries"."rowid" '
            . 'from "telescope_entries" where "created_at" < ? and ("type" = ?) limit 1000)',
            [Carbon::now()->subHours(25)->toDateTimeString(), EntryType::QUERY]
        );
    }

    protected function mockQueriesWithSeveralSetHoursAndHours(): void
    {
        $this->mockDelete(
            'delete from "telescope_entries" where "rowid" in (select "telescope_entries"."rowid" '
            . 'from "telescope_entries" where "created_at" < ? and ("type" = ?) limit 1000)',
            [Carbon::now()->subHours(5)->toDateTimeString(), EntryType::REQUEST],
            200
        );

        $this->mockDelete(
            'delete from "telescope_entries" where "rowid" in (select "telescope_entries"."rowid" '
            . 'from "telescope_entries" where "created_at" < ? and ("type" = ?) limit 1000)',
            [Carbon::now()->subHours(5)->toDateTimeString(), EntryType::REQUEST]
        );

        $this->mockDelete(
            'delete from "telescope_entries" where "rowid" in (select "telescope_entries"."rowid" '
            . 'from "telescope_entries" where "created_at" < ? and ("type" = ?) limit 1000)',
            [Carbon::now()->subHours(100)->toDateTimeString(), EntryType::REDIS],
            100
        );

        $this->mockDelete(
            'delete from "telescope_entries" where "rowid" in (select "telescope_entries"."rowid" '
            . 'from "telescope_entries" where "created_at" < ? and ("type" = ?) limit 1000)',
            [Carbon::now()->subHours(100)->toDateTimeString(), EntryType::REDIS]
        );

        $this->mockDelete(
            'delete from "telescope_entries" where "rowid" in (select "telescope_entries"."rowid" '
            . 'from "telescope_entries" where "created_at" < ? and ("type" = ?) limit 1000)',
            [Carbon::now()->subHours(25)->toDateTimeString(), EntryType::QUERY],
            50
        );

        $this->mockDelete(
            'delete from "telescope_entries" where "rowid" in (select "telescope_entries"."rowid" '
            . 'from "telescope_entries" where "created_at" < ? and ("type" = ?) limit 1000)',
            [Carbon::now()->subHours(25)->toDateTimeString(), EntryType::QUERY]
        );

        $this->mockDelete(
            'delete from "telescope_entries" where "rowid" in (select "telescope_entries"."rowid" '
            . 'from "telescope_entries" where "created_at" < ? and ("type" = ? or "type" = ? or '
            . '"type" = ? or "type" = ? or "type" = ? or "type" = ? or "type" = ? or "type" = ? or '
            . '"type" = ? or "type" = ? or "type" = ? or "type" = ? or "type" = ? or ("type" = ? and content::jsonb->>\'status\' = \'processed\')) limit 1000)',
            [
                Carbon::now()->subHours(80)->toDateTimeString(),
                EntryType::BATCH,
                EntryType::CACHE,
                EntryType::DUMP,
                EntryType::EVENT,
                EntryType::EXCEPTION,
                EntryType::JOB,
                EntryType::LOG,
                EntryType::MAIL,
                EntryType::MODEL,
                EntryType::NOTIFICATION,
                EntryType::SCHEDULED_TASK,
                EntryType::GATE,
                EntryType::VIEW,
                EntryType::JOB,
            ],
            123
        );

        $this->mockDelete(
            'delete from "telescope_entries" where "rowid" in (select "telescope_entries"."rowid" '
            . 'from "telescope_entries" where "created_at" < ? and ("type" = ? or "type" = ? or '
            . '"type" = ? or "type" = ? or "type" = ? or "type" = ? or "type" = ? or "type" = ? or '
            . '"type" = ? or "type" = ? or "type" = ? or "type" = ? or "type" = ? or '
            . '("type" = ? and content::jsonb->>\'status\' = \'processed\')) limit 1000)',
            [
                Carbon::now()->subHours(80)->toDateTimeString(),
                EntryType::BATCH,
                EntryType::CACHE,
                EntryType::DUMP,
                EntryType::EVENT,
                EntryType::EXCEPTION,
                EntryType::JOB,
                EntryType::LOG,
                EntryType::MAIL,
                EntryType::MODEL,
                EntryType::NOTIFICATION,
                EntryType::SCHEDULED_TASK,
                EntryType::GATE,
                EntryType::VIEW,
                EntryType::JOB,
            ]
        );
    }

    protected function mockQueriesWithUnresolvedException(): void
    {
        $this->mockDelete(
            'delete from "telescope_entries" where "rowid" in (select "telescope_entries"."rowid" '
            . 'from "telescope_entries" where "created_at" < ? and ("type" = ?) limit 1000)',
            [Carbon::now()->subHours(5)->toDateTimeString(), EntryType::REQUEST],
            200
        );

        $this->mockDelete(
            'delete from "telescope_entries" where "rowid" in (select "telescope_entries"."rowid" '
            . 'from "telescope_entries" where "created_at" < ? and ("type" = ?) limit 1000)',
            [Carbon::now()->subHours(5)->toDateTimeString(), EntryType::REQUEST]
        );

        $this->mockDelete(
            'delete from "telescope_entries" where "rowid" in (select "telescope_entries"."rowid" '
            . 'from "telescope_entries" where "created_at" < ? and (("type" = ? '
            . 'and content::jsonb->>\'resolved_at\' is null)) limit 1000)',
            [Carbon::now()->subHours(20)->toDateTimeString(), EntryType::EXCEPTION],
            32
        );

        $this->mockDelete(
            'delete from "telescope_entries" where "rowid" in (select "telescope_entries"."rowid" '
            . 'from "telescope_entries" where "created_at" < ? and (("type" = ? '
            . 'and content::jsonb->>\'resolved_at\' is null)) limit 1000)',
            [Carbon::now()->subHours(20)->toDateTimeString(), EntryType::EXCEPTION]
        );

        $this->mockDelete(
            'delete from "telescope_entries" where "rowid" in (select "telescope_entries"."rowid" '
            . 'from "telescope_entries" where "created_at" < ? and ("type" = ?) limit 1000)',
            [Carbon::now()->subHours(25)->toDateTimeString(), EntryType::QUERY],
            50
        );

        $this->mockDelete(
            'delete from "telescope_entries" where "rowid" in (select "telescope_entries"."rowid" '
            . 'from "telescope_entries" where "created_at" < ? and ("type" = ?) limit 1000)',
            [Carbon::now()->subHours(25)->toDateTimeString(), EntryType::QUERY]
        );

        $this->mockDelete(
            'delete from "telescope_entries" where "rowid" in (select "telescope_entries"."rowid" '
            . 'from "telescope_entries" where "created_at" < ? and ("type" = ? or "type" = ? or '
            . '"type" = ? or "type" = ? or "type" = ? or "type" = ? or "type" = ? or "type" = ? or '
            . '"type" = ? or "type" = ? or "type" = ? or "type" = ? or "type" = ? or '
            . '("type" = ? and content::jsonb->>\'status\' = \'processed\') or '
            . '("type" = ? and content::jsonb->>\'resolved_at\' is not null)) limit 1000)',
            [
                Carbon::now()->subHours(80)->toDateTimeString(),
                EntryType::BATCH,
                EntryType::CACHE,
                EntryType::DUMP,
                EntryType::EVENT,
                EntryType::JOB,
                EntryType::LOG,
                EntryType::MAIL,
                EntryType::MODEL,
                EntryType::NOTIFICATION,
                EntryType::REDIS,
                EntryType::SCHEDULED_TASK,
                EntryType::GATE,
                EntryType::VIEW,
                EntryType::JOB,
                EntryType::EXCEPTION,
            ],
            200
        );

        $this->mockDelete(
            'delete from "telescope_entries" where "rowid" in (select "telescope_entries"."rowid" '
            . 'from "telescope_entries" where "created_at" < ? and ("type" = ? or "type" = ? or '
            . '"type" = ? or "type" = ? or "type" = ? or "type" = ? or "type" = ? or "type" = ? or '
            . '"type" = ? or "type" = ? or "type" = ? or "type" = ? or "type" = ? or '
            . '("type" = ? and content::jsonb->>\'status\' = \'processed\') or '
            . '("type" = ? and content::jsonb->>\'resolved_at\' is not null)) limit 1000)',
            [
                Carbon::now()->subHours(80)->toDateTimeString(),
                EntryType::BATCH,
                EntryType::CACHE,
                EntryType::DUMP,
                EntryType::EVENT,
                EntryType::JOB,
                EntryType::LOG,
                EntryType::MAIL,
                EntryType::MODEL,
                EntryType::NOTIFICATION,
                EntryType::REDIS,
                EntryType::SCHEDULED_TASK,
                EntryType::GATE,
                EntryType::VIEW,
                EntryType::JOB,
                EntryType::EXCEPTION,
            ]
        );
    }

    protected function mockQueriesWithResolvedExceptionWithoutHours(): void
    {
        $this->mockDelete(
            'delete from "telescope_entries" where "rowid" in (select "telescope_entries"."rowid" '
            . 'from "telescope_entries" where "created_at" < ? and ("type" = ?) limit 1000)',
            [Carbon::now()->subHours(5)->toDateTimeString(), EntryType::REQUEST],
            200
        );

        $this->mockDelete(
            'delete from "telescope_entries" where "rowid" in (select "telescope_entries"."rowid" '
            . 'from "telescope_entries" where "created_at" < ? and ("type" = ?) limit 1000)',
            [Carbon::now()->subHours(5)->toDateTimeString(), EntryType::REQUEST]
        );

        $this->mockDelete(
            'delete from "telescope_entries" where "rowid" in (select "telescope_entries"."rowid" '
            . 'from "telescope_entries" where "created_at" < ? and (("type" = ? '
            . 'and content::jsonb->>\'resolved_at\' is not null)) limit 1000)',
            [Carbon::now()->subHours(10)->toDateTimeString(), EntryType::EXCEPTION],
            15
        );

        $this->mockDelete(
            'delete from "telescope_entries" where "rowid" in (select "telescope_entries"."rowid" '
            . 'from "telescope_entries" where "created_at" < ? and (("type" = ? '
            . 'and content::jsonb->>\'resolved_at\' is not null)) limit 1000)',
            [Carbon::now()->subHours(10)->toDateTimeString(), EntryType::EXCEPTION]
        );

        $this->mockDelete(
            'delete from "telescope_entries" where "rowid" in (select "telescope_entries"."rowid" '
            . 'from "telescope_entries" where "created_at" < ? and ("type" = ?) limit 1000)',
            [Carbon::now()->subHours(25)->toDateTimeString(), EntryType::QUERY],
            50
        );

        $this->mockDelete(
            'delete from "telescope_entries" where "rowid" in (select "telescope_entries"."rowid" '
            . 'from "telescope_entries" where "created_at" < ? and ("type" = ?) limit 1000)',
            [Carbon::now()->subHours(25)->toDateTimeString(), EntryType::QUERY]
        );
    }

    protected function mockQueriesWithCompletedJob(): void
    {
        $this->mockDelete(
            'delete from "telescope_entries" where "rowid" in (select "telescope_entries"."rowid" '
            . 'from "telescope_entries" where "created_at" < ? and ("type" = ?) limit 1000)',
            [Carbon::now()->subHours(5)->toDateTimeString(), EntryType::REQUEST],
            200,
        );

        $this->mockDelete(
            'delete from "telescope_entries" where "rowid" in (select "telescope_entries"."rowid" '
            . 'from "telescope_entries" where "created_at" < ? and ("type" = ?) limit 1000)',
            [Carbon::now()->subHours(5)->toDateTimeString(), EntryType::REQUEST],
        );

        $this->mockDelete(
            'delete from "telescope_entries" where "rowid" in (select "telescope_entries"."rowid" from "telescope_entries" '
            . 'where "created_at" < ? and (("type" = ? and content::jsonb->>\'status\' = \'processed\')) limit 1000)',
            [Carbon::now()->subHours(10)->toDateTimeString(), EntryType::JOB],
            15,
        );

        $this->mockDelete(
            'delete from "telescope_entries" where "rowid" in (select "telescope_entries"."rowid" from "telescope_entries" '
            . 'where "created_at" < ? and (("type" = ? and content::jsonb->>\'status\' = \'processed\')) limit 1000)',
            [Carbon::now()->subHours(10)->toDateTimeString(), EntryType::JOB],
        );

        $this->mockDelete(
            'delete from "telescope_entries" where "rowid" in (select "telescope_entries"."rowid" '
            . 'from "telescope_entries" where "created_at" < ? and ("type" = ?) limit 1000)',
            [Carbon::now()->subHours(25)->toDateTimeString(), EntryType::QUERY],
            50,
        );

        $this->mockDelete(
            'delete from "telescope_entries" where "rowid" in (select "telescope_entries"."rowid" '
            . 'from "telescope_entries" where "created_at" < ? and ("type" = ?) limit 1000)',
            [Carbon::now()->subHours(25)->toDateTimeString(), EntryType::QUERY],
        );

        $this->mockDelete(
            'delete from "telescope_entries" where "rowid" in (select "telescope_entries"."rowid" '
            . 'from "telescope_entries" where "created_at" < ? and ("type" = ? or "type" = ? or '
            . '"type" = ? or "type" = ? or "type" = ? or "type" = ? or "type" = ? or "type" = ? or '
            . '"type" = ? or "type" = ? or "type" = ? or "type" = ? or "type" = ? or "type" = ?) limit 1000)',
            [
                Carbon::now()->subHours(80)->toDateTimeString(),
                EntryType::BATCH,
                EntryType::CACHE,
                EntryType::DUMP,
                EntryType::EVENT,
                EntryType::EXCEPTION,
                EntryType::JOB,
                EntryType::LOG,
                EntryType::MAIL,
                EntryType::MODEL,
                EntryType::NOTIFICATION,
                EntryType::REDIS,
                EntryType::SCHEDULED_TASK,
                EntryType::GATE,
                EntryType::VIEW,
            ],
            200,
        );

        $this->mockDelete(
            'delete from "telescope_entries" where "rowid" in (select "telescope_entries"."rowid" '
            . 'from "telescope_entries" where "created_at" < ? and ("type" = ? or "type" = ? or '
            . '"type" = ? or "type" = ? or "type" = ? or "type" = ? or "type" = ? or "type" = ? or '
            . '"type" = ? or "type" = ? or "type" = ? or "type" = ? or "type" = ? or "type" = ?) limit 1000)',
            [
                Carbon::now()->subHours(80)->toDateTimeString(),
                EntryType::BATCH,
                EntryType::CACHE,
                EntryType::DUMP,
                EntryType::EVENT,
                EntryType::EXCEPTION,
                EntryType::JOB,
                EntryType::LOG,
                EntryType::MAIL,
                EntryType::MODEL,
                EntryType::NOTIFICATION,
                EntryType::REDIS,
                EntryType::SCHEDULED_TASK,
                EntryType::GATE,
                EntryType::VIEW,
            ],
        );
    }

    protected function mockQueriesWithCompletedJobWithoutHours(): void
    {
        $this->mockDelete(
            'delete from "telescope_entries" where "rowid" in (select "telescope_entries"."rowid" '
            . 'from "telescope_entries" where "created_at" < ? and ("type" = ?) limit 1000)',
            [Carbon::now()->subHours(5)->toDateTimeString(), EntryType::REQUEST],
            200,
        );

        $this->mockDelete(
            'delete from "telescope_entries" where "rowid" in (select "telescope_entries"."rowid" '
            . 'from "telescope_entries" where "created_at" < ? and ("type" = ?) limit 1000)',
            [Carbon::now()->subHours(5)->toDateTimeString(), EntryType::REQUEST],
        );

        $this->mockDelete(
            'delete from "telescope_entries" where "rowid" in (select "telescope_entries"."rowid" from "telescope_entries" '
            . 'where "created_at" < ? and (("type" = ? and content::jsonb->>\'status\' = \'processed\')) limit 1000)',
            [Carbon::now()->subHours(10)->toDateTimeString(), EntryType::JOB],
            15,
        );

        $this->mockDelete(
            'delete from "telescope_entries" where "rowid" in (select "telescope_entries"."rowid" from "telescope_entries" '
            . 'where "created_at" < ? and (("type" = ? and content::jsonb->>\'status\' = \'processed\')) limit 1000)',
            [Carbon::now()->subHours(10)->toDateTimeString(), EntryType::JOB],
        );

        $this->mockDelete(
            'delete from "telescope_entries" where "rowid" in (select "telescope_entries"."rowid" '
            . 'from "telescope_entries" where "created_at" < ? and ("type" = ?) limit 1000)',
            [Carbon::now()->subHours(25)->toDateTimeString(), EntryType::QUERY],
            50,
        );

        $this->mockDelete(
            'delete from "telescope_entries" where "rowid" in (select "telescope_entries"."rowid" '
            . 'from "telescope_entries" where "created_at" < ? and ("type" = ?) limit 1000)',
            [Carbon::now()->subHours(25)->toDateTimeString(), EntryType::QUERY],
        );
    }
}