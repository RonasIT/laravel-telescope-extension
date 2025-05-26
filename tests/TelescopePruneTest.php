<?php

namespace RonasIT\TelescopeExtension\Tests;

use Carbon\Carbon;
use Illuminate\Support\Facades\Artisan;
use RonasIT\TelescopeExtension\Tests\Support\TelescopePruneTestTrait;

class TelescopePruneTest extends TestCase
{
    use TelescopePruneTestTrait;

    protected string $testNow = '2018-11-11 11:11:11';

    public function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow(Carbon::parse($this->testNow));
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

    public function testPruneWithUnresolvedException()
    {
        $this->mockQueriesWithUnresolvedException();

        $this->artisan('telescope:prune --set-hours=request:5,unresolved_exception:20,query:25 --hours=80')
            ->expectsOutput("Pruning records of type 'request' older than 5 hours...")
            ->expectsOutput('Deleted 200 records.')
            ->expectsOutput("Pruning records of type 'unresolved_exception' older than 20 hours...")
            ->expectsOutput('Deleted 32 records.')
            ->expectsOutput("Pruning records of type 'query' older than 25 hours...")
            ->expectsOutput('Deleted 50 records.')
            ->expectsOutput('Pruning records of other types older than 80 hours...')
            ->expectsOutput('Deleted 200 records.')
            ->assertExitCode(0);
    }

    public function testPruneWithResolvedExceptionWithoutHours()
    {
        $this->mockQueriesWithResolvedExceptionWithoutHours();

        $this->artisan('telescope:prune --set-hours=request:5,resolved_exception:10,query:25')
            ->expectsOutput("Pruning records of type 'request' older than 5 hours...")
            ->expectsOutput('Deleted 200 records.')
            ->expectsOutput("Pruning records of type 'resolved_exception' older than 10 hours...")
            ->expectsOutput('Deleted 15 records.')
            ->expectsOutput("Pruning records of type 'query' older than 25 hours...")
            ->expectsOutput('Deleted 50 records.')
            ->assertExitCode(0);
    }

    public function testPruneWithCompletedJob()
    {
        $this->mockQueriesWithCompletedJob();

        $this
            ->artisan('telescope:prune --set-hours=request:5,completed_job:10,query:25 --hours=80')
            ->expectsOutput("Pruning records of type 'request' older than 5 hours...")
            ->expectsOutput('Deleted 200 records.')
            ->expectsOutput("Pruning records of type 'completed_job' older than 10 hours...")
            ->expectsOutput('Deleted 15 records.')
            ->expectsOutput("Pruning records of type 'query' older than 25 hours...")
            ->expectsOutput('Deleted 50 records.')
            ->expectsOutput('Pruning records of other types older than 80 hours...')
            ->expectsOutput('Deleted 200 records.')
            ->assertExitCode(0);
    }

    public function testPruneWithCompletedJobWithoutHours()
    {
        $this->mockQueriesWithCompletedJobWithoutHours();

        $this
            ->artisan('telescope:prune --set-hours=request:5,completed_job:10,query:25')
            ->expectsOutput("Pruning records of type 'request' older than 5 hours...")
            ->expectsOutput('Deleted 200 records.')
            ->expectsOutput("Pruning records of type 'completed_job' older than 10 hours...")
            ->expectsOutput('Deleted 15 records.')
            ->expectsOutput("Pruning records of type 'query' older than 25 hours...")
            ->expectsOutput('Deleted 50 records.')
            ->assertExitCode(0);
    }

    public function testPruneValidateSetHoursType()
    {
        $this->expectException('Exception');
        $this->expectExceptionMessage("Incorrect type value 'incorrect'.");

        $this->artisan('telescope:prune --set-hours=query:12,incorrect:22');
    }

    public function testPruneValidateSetHoursValueNotSet()
    {
        $this->expectException('Exception');
        $this->expectExceptionMessage("Incorrect value 'request' of the 'set-hours' option.");

        $this->artisan('telescope:prune --set-hours=query:12,request');
    }

    public function testPruneValidateSetHoursValueEmpty()
    {
        $this->expectException('Exception');
        $this->expectExceptionMessage("Hours value for 'request' type must be set.");

        $this->artisan('telescope:prune --set-hours=query:12,request:');
    }

    public function testPruneValidateSetHoursValueIsNotNumber()
    {
        $this->expectException('Exception');
        $this->expectExceptionMessage("Hours value for 'request' type must be a number.");

        $this->artisan('telescope:prune --set-hours=query:12,request:ss');
    }

    public function testPruneValidateHoursValueIsNotNumber()
    {
        $this->expectException('Exception');
        $this->expectExceptionMessage('Hours hours must be a number.');

        $this->artisan('telescope:prune --set-hours=query:12,request:34 --hours=ss');
    }
}
