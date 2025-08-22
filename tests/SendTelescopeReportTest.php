<?php

use Carbon\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Artisan;
use RonasIT\TelescopeExtension\Tests\TestCase;
use RonasIT\TelescopeExtension\TelescopeExtensionServiceProvider;
use Illuminate\Support\Facades\Config;
use Illuminate\Console\Scheduling\Schedule;
use RonasIT\TelescopeExtension\Tests\Support\SQLMockTrait;
use Illuminate\Support\Str;

class SendTelescopeReportTest extends TestCase
{
    use SqlMockTrait;

    protected TelescopeExtensionServiceProvider $serviceProvider;

    protected Schedule $schedule;

    public function setUp(): void
    {
        parent::setUp();

        $this->serviceProvider = new TelescopeExtensionServiceProvider($this->app);

        $this->schedule = $this->app->make(Schedule::class);

        Config::set('app.name', 'Test app name');
    }

    public function testCommandInTheList()
    {
        $this->assertArrayHasKey('telescope:send-report', Artisan::all());
    }

    public function testCommandEnabledAndRun(): void
    {
        Config::set('telescope.notifications.report.enabled', true);
        Config::set('telescope.notifications.report.frequency', 7);
        Config::set('telescope.notifications.report.time', '15');

        $this->serviceProvider->boot();

        $event = Arr::first($this->schedule->events());

        $this->assertTrue(Str::endsWith($event->command, 'telescope:send-report'));

        $this->assertEquals('0 15 * * *', $event->getExpression());

        $filters = $this->getProtectedProperty($event, 'filters');
        $filterClosure = Arr::first($filters);

        Carbon::setTestNow(Carbon::create(2018, 1, 14));

        $this->assertTrue($filterClosure());
    }

    public function testCommandEnabledNotRun(): void
    {
        Config::set('telescope.notifications.report.enabled', true);
        Config::set('telescope.notifications.report.frequency', 8);
        Config::set('telescope.notifications.report.time', '15');

        $this->serviceProvider->boot();

        $event = Arr::first($this->schedule->events());

        $this->assertTrue(Str::endsWith($event->command, 'telescope:send-report'));

        $this->assertEquals('0 15 * * *', $event->getExpression());

        $filters = $this->getProtectedProperty($event, 'filters');
        $filterClosure = Arr::first($filters);

        Carbon::setTestNow(Carbon::create(2018, 1, 7));

        $this->assertFalse($filterClosure());
    }

    public function testCommandDisabled(): void
    {
        Config::set('telescope.notifications.report.enabled', false);

        $this->serviceProvider->boot();

        $this->assertCount(0, $this->schedule->events());
    }

    public function testCommand()
    {
        Config::set('telescope.notifications.report.drivers.mail.mail_to', 'test@mail.com');

        $statementMock = Mockery::mock(PDOStatement::class);

        $statementMock
            ->shouldReceive('fetchAll')
            ->once()
            ->andReturn($this->getJsonFixture('fetch_all_response'));

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
            ->with('select type, count(*) as count from "telescope_entries" group by "type"')
            ->andReturn($statementMock);

        $this->artisan('telescope:send-report');

        $this->assertNotificationSent('command');
    }

    protected function getProtectedProperty($object, string $propertyName)
    {
        $reflector = new ReflectionClass($object);
        $property = $reflector->getProperty($propertyName);
        $property->setAccessible(true);
        return $property->getValue($object);
    }
}
