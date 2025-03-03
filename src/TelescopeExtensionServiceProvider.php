<?php

namespace RonasIT\TelescopeExtension;

use Illuminate\Foundation\Console\AboutCommand;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\ServiceProvider;
use Laravel\Telescope\Contracts\ClearableRepository;
use Laravel\Telescope\Contracts\EntriesRepository;
use Laravel\Telescope\Contracts\PrunableRepository;
use RonasIT\TelescopeExtension\Console\Commands\TelescopePrune;
use RonasIT\TelescopeExtension\Repositories\TelescopeRepository;

class TelescopeExtensionServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        AboutCommand::add('Telescope Extension', fn () => ['Version' => '0.1.0']);

        if ($this->app->runningInConsole()) {
            $this->commands([
                TelescopePrune::class,
            ]);
        }

        $this->publishesMigrations([
            __DIR__ . '/../migrations' => database_path('migrations'),
        ]);

        Artisan::call('migrate');
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