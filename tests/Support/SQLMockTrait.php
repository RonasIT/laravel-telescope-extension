<?php

namespace RonasIT\TelescopeExtension\Tests\Support;

use Mpyw\LaravelDatabaseMock\Facades\DBMock;
use Mpyw\LaravelDatabaseMock\Proxies\SingleConnectionProxy;

trait SQLMockTrait
{
    protected SingleConnectionProxy $pdo;

    protected function mockDelete(string $sql, array $bindings = [], ?int $rowCount = 0): void
    {
        $this->getPdo()->shouldDeleteForRows($sql, $bindings, $rowCount);
    }

    protected function getPdo(): SingleConnectionProxy
    {
        $this->pdo ??= DBMock::mockPdo();

        return $this->pdo;
    }
}
