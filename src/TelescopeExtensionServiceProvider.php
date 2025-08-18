<?php

namespace RonasIT\TelescopeExtension;

use Illuminate\Foundation\Console\AboutCommand;
use Illuminate\Support\ServiceProvider;
use Laravel\Telescope\Contracts\ClearableRepository;
use Laravel\Telescope\Contracts\EntriesRepository;
use Laravel\Telescope\Contracts\PrunableRepository;
use RonasIT\TelescopeExtension\Console\Commands\SendTelescopeReport;
use RonasIT\TelescopeExtension\Console\Commands\TelescopePrune;
use RonasIT\TelescopeExtension\Repositories\TelescopeRepository;
use Illuminate\Console\Scheduling\Schedule;

class TelescopeExtensionServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        AboutCommand::add('Telescope Extension', fn () => ['Version' => '0.1.0']);

        $this->publishes([
            __DIR__ . '/../config/telescope.php' => config_path('telescope.php'),
            __DIR__ . '/../config/notifications.php' => config_path('notifications.php'),
        ], 'config');

        $this->mergeConfigFrom(__DIR__ . '/../config/telescope.php', 'telescope');
        $this->mergeConfigFrom(__DIR__ . '/../config/telescope-guzzle-watcher.php', 'telescope-guzzle-watcher');
        $this->mergeConfigFrom(__DIR__ . '/../config/notifications.php', 'notifications.telescope');

        $this->publishes([
            __DIR__ . '/../config/telescope-guzzle-watcher.php' => config_path('telescope-guzzle-watcher.php'),
        ], 'config');

        $this->publishes([
            __DIR__ . '/../resources/views/report.blade.php' => resource_path('views/vendor/telescope/report.blade.php'),
        ], 'view');

        if ($this->app->runningInConsole()) {
            $this->commands([
                TelescopePrune::class,
                SendTelescopeReport::class,
            ]);
        }

        $this->loadMigrationsFrom(__DIR__ . '/../migrations');

        $this->loadRoutesFrom(__DIR__ . '/../routes/telescope.php');

        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'telescope');

        $this->app->booted(function () {
            $schedule = $this->app->make(Schedule::class);

            if (config('notifications.report.enabled')) {
                $frequency = config('notifications.report.frequency');
                $time = config('notifications.report.time');

                $schedule->command('telescope:report')
                    ->dailyAt("{$time}:00")
                    ->when(fn () => now()->dayOfYear % $frequency == 0);
            }
        });
    }

    public function register(): void
    {
        $this->registerDatabaseDriver();
    }

    protected function registerDatabaseDriver(): void
    {
        $this->app->singleton(
            EntriesRepository::class, TelescopeRepository::class
        );

        $this->app->singleton(
            ClearableRepository::class, TelescopeRepository::class
        );

        $this->app->singleton(
            PrunableRepository::class, TelescopeRepository::class
        );

        $this->app->when(TelescopeRepository::class)
            ->needs('$connection')
            ->give(config('telescope.storage.database.connection'));

        $this->app->when(TelescopeRepository::class)
            ->needs('$chunkSize')
            ->give(config('telescope.storage.database.chunk'));
    }
}