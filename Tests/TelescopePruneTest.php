<?php

namespace RonasIT\TelescopeExtension\Tests;

use Carbon\Carbon;
use Illuminate\Support\Facades\Artisan;
use Laravel\Telescope\Storage\DatabaseEntriesRepository;
use Orchestra\Testbench\TestCase;
use RonasIT\TelescopeExtension\Repositories\TelescopeRepository;
use RonasIT\TelescopeExtension\TelescopeExtensionServiceProvider;
use RonasIT\TelescopeExtension\Tests\Traits\SqlMockTrait;

class TelescopePruneTest extends TestCase
{
    use SqlMockTrait;

    protected string $testNow = '2018-11-11 11:11:11';

    public function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow(Carbon::parse($this->testNow));
    }

    protected function getPackageProviders($app): array
    {
        return [
            TelescopeExtensionServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app): void
    {
        parent::getEnvironmentSetUp($app);

        $config = $app->get('config');

        $config->set('logging.default', 'errorlog');

        $config->set('database.default', 'testbench');

        $config->set('telescope.storage.database.connection', 'testbench');

        $config->set('queue.batching.database', 'testbench');

        $config->set('database.connections.testbench', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);

        //$app->when(DatabaseEntriesRepository::class)
        $app->when(TelescopeRepository::class)
            ->needs('$connection')
            ->give('testbench');
    }

    public function testCommandInTheList()
    {
        $commands = Artisan::all();

        $this->assertArrayHasKey('telescope:prune', $commands);
    }

    public function testPruneWithoutParameters()
    {
        $this->mockQueriesWithoutParameters();

        $this->artisan('telescope:prune')
            ->expectsOutput('Deleted all records.')
            ->assertExitCode(0);
    }

    public function testPruneWithOnlyHours()
    {
        $this->mockQueriesWithOnlyHours();

        $this->artisan('telescope:prune --hours=2')
            ->expectsOutput('Pruning records of other types older than 2 hours...')
            ->expectsOutput('Deleted 1123 records.')
            ->assertExitCode(0);
    }

    public function testPruneWithSingleSetHours()
    {
        $this->mockQueriesWithSingleSetHours();

        $this->artisan('telescope:prune --set-hours=request:5')
            ->expectsOutput("Pruning records of type 'request' older than 5 hours...")
            ->expectsOutput('Deleted 200 records.')
            ->assertExitCode(0);
    }

    public function testPruneWithSeveralSetHours()
    {
        $this->mockQueriesWithSeveralSetHours();

        $this->artisan('telescope:prune --set-hours=request:5,redis:100,query:25')
            ->expectsOutput("Pruning records of type 'request' older than 5 hours...")
            ->expectsOutput('Deleted 200 records.')
            ->expectsOutput("Pruning records of type 'redis' older than 100 hours...")
            ->expectsOutput('Deleted 100 records.')
            ->expectsOutput("Pruning records of type 'query' older than 25 hours...")
            ->expectsOutput('Deleted 50 records.')
            ->assertExitCode(0);
    }

    public function testPruneWithSeveralSetHoursAndHours()
    {
        $this->mockQueriesWithSeveralSetHoursAndHours();

        $this->artisan('telescope:prune --set-hours=request:5,redis:100,query:25 --hours=80')
            ->expectsOutput("Pruning records of type 'request' older than 5 hours...")
            ->expectsOutput('Deleted 200 records.')
            ->expectsOutput("Pruning records of type 'redis' older than 100 hours...")
            ->expectsOutput('Deleted 100 records.')
            ->expectsOutput("Pruning records of type 'query' older than 25 hours...")
            ->expectsOutput('Deleted 50 records.')
            ->expectsOutput('Pruning records of other types older than 80 hours...')
            ->expectsOutput('Deleted 123 records.')
            ->assertExitCode(0);
    }

    /*public function testPruneRequestsAndOthers()
    {
        Artisan::call('telescope:prune --set-hours=requests:6 --hours=2');

    }*/
}
