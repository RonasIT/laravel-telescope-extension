<?php

use Carbon\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Artisan;
use RonasIT\TelescopeExtension\Tests\TestCase;
use RonasIT\TelescopeExtension\TelescopeExtensionServiceProvider;
use Illuminate\Support\Facades\Config;
use Illuminate\Console\Scheduling\Schedule;
use RonasIT\TelescopeExtension\Mail\ReportMail;
use Illuminate\Support\Facades\Mail;
use RonasIT\TelescopeExtension\Tests\Support\SendTelescopeReportTestTrait;

class SendTelescopeReportTest extends TestCase
{
    use SendTelescopeReportTestTrait;

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

        Carbon::setTestNow(Carbon::create(2018, 1, 14));

        $this->serviceProvider->boot();

        $event = Arr::first($this->schedule->events());

        $this->assertScheduledEventEquals($event, 'telescope:send-report', '0 15 * * *');

        $this->assertScheduledEventExecuted($event, true);
    }

    public function testCommandEnabledNotRun(): void
    {
        Config::set('telescope.notifications.report.enabled', true);
        Config::set('telescope.notifications.report.frequency', 8);
        Config::set('telescope.notifications.report.time', '15');

        Carbon::setTestNow(Carbon::create(2018, 1, 7));

        $this->serviceProvider->boot();

        $event = Arr::first($this->schedule->events());

        $this->assertScheduledEventEquals($event, 'telescope:send-report', '0 15 * * *');

        $this->assertScheduledEventExecuted($event, false);
    }

    public function testCommandDisabled(): void
    {
        Config::set('telescope.notifications.report.enabled', false);

        $this->serviceProvider->boot();

        $this->assertCount(0, $this->schedule->events());
    }

    public function testCommand()
    {
        Config::set('telescope.notifications.report.drivers.mail.to', 'test@mail.com');

        $this->mockSelectEntries();

        $this->artisan('telescope:send-report');

        $this->assertNotificationSent('command');
    }

    public function testReportMail()
    {
        Mail::to('test@mail')->send(new ReportMail(
            telescopeBaseUrl: 'http://localhost/telescope',
            entries: collect($this->getJsonFixture('entries_data')),
        ));

        $this->assertMailEquals(ReportMail::class, [
            'emails' => 'test@mail',
            'fixture' => 'mails/report.html',
        ]);
    }
}
