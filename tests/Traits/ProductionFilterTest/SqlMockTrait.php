<?php

namespace RonasIT\TelescopeExtension\Tests\Traits\ProductionFilterTest;

use Mpyw\LaravelDatabaseMock\Facades\DBMock;
use Mpyw\LaravelDatabaseMock\Proxies\SingleConnectionProxy;
use Mockery;
use PDO;
use PDOStatement;

trait SqlMockTrait
{
    protected SingleConnectionProxy $pdo;

    protected function mockSelect(): void
    {
        $statementMock = Mockery::mock(PDOStatement::class);

        $statementMock
            ->shouldReceive('fetchAll')
            ->twice()
            ->andReturn([
                ['tag' => 'test'],
                ['tag' => 'test 2'],
            ]);

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

    protected function getPdo(): SingleConnectionProxy
    {
        $this->pdo ??= DBMock::mockPdo();

        return $this->pdo;
    }
}