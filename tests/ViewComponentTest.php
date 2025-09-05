<?php

namespace RonasIT\TelescopeExtension\Tests;

use Illuminate\Support\Facades\DB;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use RonasIT\TelescopeExtension\Tests\Support\SQLMockTrait;
use RonasIT\TelescopeExtension\View\Components\EntriesCount;
use Illuminate\Database\Query\Builder;

class ViewComponentTest extends TestCase
{
    use MockeryPHPUnitIntegration;
    use SQLMockTrait;

    /**
     * @dataProvider entriesCountDataProvider
     */
    public function testEntriesCount(string $type, string $label, int $rowCount, string $expected): void
    {
        $queryMock = Mockery::mock(Builder::class);

        $queryMock->shouldReceive('where')
                  ->with('type', $type)
                  ->andReturnSelf();

        $queryMock->shouldReceive('count')
                  ->andReturn($rowCount);

        DB::shouldReceive('table')
            ->once()
            ->with('telescope_entries')
            ->andReturn($queryMock);

        $component = new EntriesCount($type, $label);
        
        $result = $component->render();

        $this->assertSame($expected, $result);
    }

    public static function entriesCountDataProvider(): array
    {
        return [
            'with entries' => ['request', 'Requests', 5, 'Requests (5)'],
            'without entries' => ['command', 'Commands', 0, 'Commands'],
        ];
    }
}
