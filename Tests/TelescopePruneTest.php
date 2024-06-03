<?php

namespace RonasIT\TelescopeExtension\Tests;

use RonasIT\TelescopeExtension\Tests\TestCase;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Artisan;

class TelescopePruneTest extends TestCase
{
    protected static Collection $originData;
    protected static Collection $originReadingProgressData;

    public function setUp() : void
    {
        parent::setUp();

        $this->setGlobalExportMode();

        self::$originData ??= $this->getDataSet('telescope_entries');
        self::$originTelescopeMonitoring ??= $this->getDataSet('telescope_monitoring');
    }

    public function testPruneWithoutParameters()
    {
        Artisan::call('telescope:prune');

        $this->assertChangesEqualsFixture(
            'telescope_entries',
            'prune_without_parameters.json',
            self::$originData
        );
    }

    public function testPruneRequests()
    {
        Artisan::call('telescope:prune --set-hours=requests:2');

        $this->assertChangesEqualsFixture(
            'telescope_entries',
            'prune_requests.json',
            self::$originData
        );
    }

    public function testPruneAll()
    {
        Artisan::call('telescope:prune --hours=2');

        $this->assertChangesEqualsFixture(
            'telescope_entries',
            'prune_all.json',
            self::$originData
        );
    }

    public function testPruneRequestsAndOthers()
    {
        Artisan::call('telescope:prune --set-hours=requests:6 --hours=2');

        $this->assertChangesEqualsFixture(
            'telescope_entries',
            'prune_requests_and_others.json',
            self::$originData
        );
    }
}
