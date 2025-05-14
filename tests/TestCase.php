<?php

namespace RonasIT\TelescopeExtension\Tests;

use Orchestra\Testbench\TestCase as BaseTestCase;
use RonasIT\TelescopeExtension\Repositories\TelescopeRepository;
use RonasIT\TelescopeExtension\TelescopeExtensionServiceProvider;

class TestCase extends BaseTestCase
{
    protected function mockEnvironment(string $environment): void
    {
        $this->app->detectEnvironment(fn () => $environment);
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

        $app->when(TelescopeRepository::class)
            ->needs('$connection')
            ->give('testbench');
    }
}
