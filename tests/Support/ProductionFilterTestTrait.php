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
            ->twice()
            ->andReturn([$returnData]);

        $statementMock
            ->shouldReceive('execute')
            ->twice()
            ->andReturnTrue();

        $statementMock
            ->shouldReceive('setFetchMode')
            ->twice()
            ->with(PDO::PARAM_BOOL)
            ->andReturnTrue();

        $this
            ->getPdo()
            ->shouldReceive('prepare')
            ->twice()
            ->with('select "tag" from "telescope_monitoring"')
            ->andReturn($statementMock);
    }
}