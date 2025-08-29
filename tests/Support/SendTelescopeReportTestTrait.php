<?php

namespace RonasIT\TelescopeExtension\Tests\Support;

use Mockery;
use PDOStatement;
use PDO;

trait SendTelescopeReportTestTrait
{
    use SQLMockTrait;

    protected function mockSelectEntries(): void
    {
        $statementMock = Mockery::mock(PDOStatement::class);

        $statementMock
            ->shouldReceive('fetchAll')
            ->once()
            ->andReturn($this->getJsonFixture('sql_responses/fetch_all_response'));

        $statementMock
            ->shouldReceive('execute')
            ->once()
            ->andReturnTrue();

        $statementMock
            ->shouldReceive('setFetchMode')
            ->once()
            ->with(PDO::PARAM_BOOL)
            ->andReturnTrue();

        $this
            ->getPdo()
            ->shouldReceive('prepare')
            ->once()
            ->with('select type, count(*) as count from "telescope_entries" group by "type"')
            ->andReturn($statementMock);
    }
}
