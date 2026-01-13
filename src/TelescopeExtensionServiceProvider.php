<?php

namespace RonasIT\TelescopeExtension;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\AboutCommand;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use Laravel\Telescope\Contracts\ClearableRepository;
use Laravel\Telescope\Contracts\EntriesRepository;
use Laravel\Telescope\Contracts\PrunableRepository;
use RonasIT\Support\Http\Middleware\CheckIpMiddleware;
use RonasIT\TelescopeExtension\Console\Commands\SendTelescopeReport;
use RonasIT\TelescopeExtension\Console\Commands\TelescopePrune;
use RonasIT\TelescopeExtension\Repositories\TelescopeRepository;
use RonasIT\TelescopeExtension\View\Components\EntriesCount;

class TelescopeExtensionServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        AboutCommand::add('Telescope Extension', fn () => ['Version' => '0.1.0']);

        $this->publishes([
            __DIR__ . '/../config/telescope.php' => config_path('telescope.php'),
            __DIR__ . '/../config/telescope-guzzle-watcher.php' => config_path('telescope-guzzle-watcher.php'),
        ], 'config');

        $this->mergeConfigFrom(__DIR__ . '/../config/telescope.php', 'telescope');
        $this->mergeConfigFrom(__DIR__ . '/../config/telescope-guzzle-watcher.php', 'telescope-guzzle-watcher');

        $this->publishes([
            __DIR__ . '/../resources/views/emails/report.blade.php' => resource_path('views/vendor/telescope/report.blade.php'),
        ], 'view');

        if ($this->app->runningInConsole()) {
            $this->commands([
                TelescopePrune::class,
                SendTelescopeReport::class,
            ]);
        }

        $this->loadMigrationsFrom(__DIR__ . '/../migrations');

        $this->loadRoutesFrom(__DIR__ . '/../routes/telescope.php');

        $this->callAfterResolving('view', fn ($view) => $view->prependNamespace('telescope', __DIR__ . '/../resources/views'));

        Blade::component('entries-count', EntriesCount::class);

        $this->app->booted(fn () => $this->scheduleTelescopeReport());
    }

    public function register(): void
    {
        $this->registerDatabaseDriver();

        $this->registerCheckIpMiddleware();
    }

    protected function registerDatabaseDriver(): void
    {
        $this->app->singleton(
            EntriesRepository::class, TelescopeRepository::class,
        );

        $this->app->singleton(
            ClearableRepository::class, TelescopeRepository::class,
        );

        $this->app->singleton(
            PrunableRepository::class, TelescopeRepository::class,
        );

        $this->app->when(TelescopeRepository::class)
            ->needs('$connection')
            ->give(config('telescope.storage.database.connection'));

        $this->app->when(TelescopeRepository::class)
            ->needs('$chunkSize')
            ->give(config('telescope.storage.database.chunk'));
    }

    protected function registerCheckIpMiddleware(): void
    {
        $allowedIps = config('telescope.allowed_ips');

        if (!empty($allowedIps)) {
            config(['telescope.middleware' => [
                ...config('telescope.middleware'),
                CheckIpMiddleware::class . ':' . implode(',', $allowedIps),
            ]]);
        }
    }

    protected function scheduleTelescopeReport(): void
    {
        $schedule = $this->app->make(Schedule::class);

        if (config('telescope.notifications.report.enabled')) {
            $frequency = config('telescope.notifications.report.frequency');
            $time = config('telescope.notifications.report.time');

            $schedule
                ->command('telescope:send-report')
                ->dailyAt("{$time}:00")
                ->when(fn () => now()->dayOfYear % $frequency == 0);
        }
    }
}
