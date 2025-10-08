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
            ->shouldReceive('bindValue')
            ->times(5)
            ->andReturnTrue();

        $statementMock
            ->shouldReceive('execute')
            ->once()
            ->withNoArgs()
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
            ->with('select type, count(*) as count from "telescope_entries" where ("type" not in (?, ?) or ("type" = ? and ' || '"content" is null) or ("type" = ? and "content" is not null)) group by "type"')
            ->andReturn($statementMock);
    }
}
