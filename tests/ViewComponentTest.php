<?php

namespace RonasIT\TelescopeExtension\Tests;

use PHPUnit\Framework\Attributes\DataProvider;
use RonasIT\TelescopeExtension\View\Components\EntriesCount;
use RonasIT\TelescopeExtension\Tests\Support\ViewComponentTestTrait;

class ViewComponentTest extends TestCase
{
    use ViewComponentTestTrait;

    public static function entriesCountDataProvider(): array
    {
        return [
            'with entries' => ['request', 'Requests', 5, 'Requests (5)'],
            'without entries' => ['command', 'Commands', 0, 'Commands'],
            'without label' => ['batch', null, 0, 'Batches'],
        ];
    }

    #[DataProvider('entriesCountDataProvider')]
    public function testEntriesCount(string $type, ?string $label, int $rowCount, string $expected): void
    {
        $this->mockEntriesCount($type, $rowCount);

        $component = new EntriesCount($type, $label);

        $result = $component->render();

        $this->assertSame($expected, $result);
    }

    public function testExeptionsCount(): void
    {
        $this->mockExceptionsCount(5);

        $component = new EntriesCount('exception');

        $result = $component->render();

        $this->assertSame('Exceptions (5)', $result);
    }
}
