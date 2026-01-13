<?php

namespace RonasIT\TelescopeExtension\Tests\Support;

use Mockery;
use PDO;
use PDOStatement;

trait ProductionFilterTestTrait
{
    use SQLMockTrait;

    protected function mockSelectTags(array $returnData): void
    {
        $statementMock = Mockery::mock(PDOStatement::class);

        $statementMock
            ->shouldReceive('fetchAll')
            ->once()
            ->andReturn([$returnData]);

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
            ->with('select "tag" from "telescope_monitoring"')
            ->andReturn($statementMock);
    }
}
