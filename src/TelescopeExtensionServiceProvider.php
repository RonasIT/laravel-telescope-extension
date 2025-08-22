<?php

namespace RonasIT\TelescopeExtension;

use Illuminate\Foundation\Console\AboutCommand;
use Illuminate\Support\ServiceProvider;
use Laravel\Telescope\Contracts\ClearableRepository;
use Laravel\Telescope\Contracts\EntriesRepository;
use Laravel\Telescope\Contracts\PrunableRepository;
use RonasIT\Support\Http\Middleware\CheckIpMiddleware;
use RonasIT\TelescopeExtension\Console\Commands\TelescopePrune;
use RonasIT\TelescopeExtension\Repositories\TelescopeRepository;

class TelescopeExtensionServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        AboutCommand::add('Telescope Extension', fn () => ['Version' => '0.1.0']);

        $this->publishes([
            __DIR__ . '/../config/telescope.php' => config_path('telescope.php'),
        ], 'config');

        $this->mergeConfigFrom(__DIR__ . '/../config/telescope.php', 'telescope');
        $this->mergeConfigFrom(__DIR__ . '/../config/telescope-guzzle-watcher.php', 'telescope-guzzle-watcher');

        $this->publishes([
            __DIR__ . '/../config/telescope-guzzle-watcher.php' => config_path('telescope-guzzle-watcher.php'),
        ], 'config');

        if ($this->app->runningInConsole()) {
            $this->commands([
                TelescopePrune::class,
            ]);
        }

        $this->loadMigrationsFrom(__DIR__ . '/../migrations');

        $this->loadRoutesFrom(__DIR__ . '/../routes/telescope.php');
    }

    public function register(): void
    {
        $this->registerDatabaseDriver();

        $this->registerCheckIpMiddleware();
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
}