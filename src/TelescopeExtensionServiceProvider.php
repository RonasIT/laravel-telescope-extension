<?php

namespace RonasIT\TelescopeExtension;

use Illuminate\Foundation\Console\AboutCommand;
use Illuminate\Support\ServiceProvider;
use Laravel\Telescope\Contracts\ClearableRepository;
use Laravel\Telescope\Contracts\EntriesRepository;
use Laravel\Telescope\Contracts\PrunableRepository;
use RonasIT\TelescopeExtension\Console\Commands\TelescopePrune;
use RonasIT\TelescopeExtension\Filters\TelescopeFilter;
use RonasIT\TelescopeExtension\Repositories\TelescopeRepository;
use Laravel\Telescope\Telescope;

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
    }

    public function register(): void
    {
        $this->registerDatabaseDriver();

        Telescope::filter((new TelescopeFilter())->apply());
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

        /*$this->app->singleton(
            PruneCommand::class, TelescopePrune::class
        );*/

        $this->app->when(TelescopeRepository::class)
            ->needs('$connection')
            ->give(config('telescope.storage.database.connection'));

        $this->app->when(TelescopeRepository::class)
            ->needs('$chunkSize')
            ->give(config('telescope.storage.database.chunk'));
    }
}