<?php

namespace RonasIT\TelescopeExtension\Tests\Support;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Builder;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use RonasIT\TelescopeExtension\Tests\Support\SQLMockTrait;

trait ViewComponentTestTrait
{
    use MockeryPHPUnitIntegration;
    use SQLMockTrait;

    public function mockEntriesCount(string $type, int $count): void
    {
        $queryMock = Mockery::mock(Builder::class);

        $queryMock->shouldReceive('where')
                ->with('type', $type)
                ->andReturnSelf();

        $queryMock->shouldReceive('count')
                ->andReturn($count);

        DB::shouldReceive('connection')
            ->andReturnSelf();

        DB::shouldReceive('table')
            ->once()
            ->with('telescope_entries')
            ->andReturn($queryMock);
    }
}
