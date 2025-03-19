<?php

namespace RonasIT\TelescopeExtension\Tests;

use Laravel\Telescope\EntryType;
use Laravel\Telescope\IncomingEntry;
use Laravel\Telescope\Storage\DatabaseEntriesRepository;
use Laravel\Telescope\Watchers\RequestWatcher;
use RonasIT\TelescopeExtension\Filters\ProductionFilter;
use RonasIT\TelescopeExtension\Repositories\TelescopeRepository;
use RonasIT\TelescopeExtension\TelescopeExtensionServiceProvider;
use Symfony\Component\HttpFoundation\Response;

class ProductionFilterTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config(['telescope.watchers.' . RequestWatcher::class => []]);
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

        $config->set('telescope.storage.database.connection', 'database');

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

    public function testDevEnv()
    {
        $this->mockEnvironment('development');

        $filter = new ProductionFilter();

        $closure = $filter->apply();

        $this->assertTrue($closure(new IncomingEntry([])));
    }

    public function testLocalEnv()
    {
        $this->mockEnvironment('local');

        $filter = new ProductionFilter();

        $closure = $filter->apply();

        $this->assertTrue($closure(new IncomingEntry([])));
    }

    public function testExceptionProdEnv()
    {
        $this->mockEnvironment('production');

        $filter = new ProductionFilter();

        $entry = new IncomingEntry([]);
        $entry->type(EntryType::EXCEPTION);

        $closure = $filter->apply();

        $this->assertTrue($closure($entry));
    }

    public function testSuccessRequestProdEnv()
    {
        $this->mockEnvironment('production');

        $filter = new ProductionFilter();

        $entry = new IncomingEntry([]);
        $entry->type(EntryType::REQUEST);

        $closure = $filter->apply();

        $this->assertFalse($closure($entry));
    }

    public function testFailedRequestProdEnv()
    {
        $this->mockEnvironment('production');

        $filter = new ProductionFilter();

        $entry = new IncomingEntry(['response_status' => Response::HTTP_BAD_REQUEST]);
        $entry->type(EntryType::EXCEPTION);

        $closure = $filter->apply();

        $this->assertTrue($closure($entry));
    }

    public function testFailedRequestIgnoreMessageProdEnv()
    {
        $this->mockEnvironment('production');

        config(['telescope.watchers.' . RequestWatcher::class => [
            'ignore_error_messages' => ['ignore_message'],
        ]]);

        $filter = new ProductionFilter();

        $entry = new IncomingEntry([
            'response_status' => Response::HTTP_BAD_REQUEST,
            'response' => ['message' => 'ignore_message'],
        ]);

        $entry->type(EntryType::REQUEST);

        $closure = $filter->apply();

        $this->assertFalse($closure($entry));
    }

    public function testFailedRequestAnotherIgnoreMessageProdEnv()
    {
        $this->mockEnvironment('production');

        config(['telescope.watchers.' . RequestWatcher::class => [
            'ignore_error_messages' => ['another_ignore_message'],
        ]]);

        $filter = new ProductionFilter();

        $entry = new IncomingEntry([
            'response_status' => Response::HTTP_BAD_REQUEST,
            'response' => ['message' => 'ignore_message'],
        ]);

        $entry->type(EntryType::REQUEST);

        $closure = $filter->apply();

        $this->assertTrue($closure($entry));
    }

    public function testSuccessClientRequestProdEnv()
    {
        $this->mockEnvironment('production');

        $filter = new ProductionFilter();

        $entry = new IncomingEntry([]);
        $entry->type(EntryType::CLIENT_REQUEST);

        $closure = $filter->apply();

        $this->assertFalse($closure($entry));
    }

    public function testFailedClientRequestProdEnv()
    {
        $this->mockEnvironment('production');

        $filter = new ProductionFilter();

        $entry = new IncomingEntry(['response_status' => Response::HTTP_BAD_REQUEST]);
        $entry->type(EntryType::CLIENT_REQUEST);

        $closure = $filter->apply();

        $this->assertTrue($closure($entry));
    }

    public function testSlowQueryProdEnv()
    {
        $this->mockEnvironment('production');

        $filter = new ProductionFilter();

        $entry = new IncomingEntry(['slow' => true]);
        $entry->type(EntryType::QUERY);

        $closure = $filter->apply();

        $this->assertTrue($closure($entry));
    }

    public function testJobProdEnv()
    {
        $this->mockEnvironment('production');

        $filter = new ProductionFilter();

        $entry = new IncomingEntry([]);
        $entry->type(EntryType::JOB);

        $closure = $filter->apply();

        $this->assertTrue($closure($entry));
    }

    public function testScheduledTaskProdEnv()
    {
        $this->mockEnvironment('production');

        $filter = new ProductionFilter();

        $entry = new IncomingEntry([]);
        $entry->type(EntryType::SCHEDULED_TASK);

        $closure = $filter->apply();

        $this->assertTrue($closure($entry));
    }

    public function testMonitoredTagProdEnv()
    {
        $this->mockEnvironment('production');

        $filter = new ProductionFilter();

        $entry = \Mockery::mock(DatabaseEntriesRepository::class);
        $entry->shouldReceive('loadMonitoredTags')->andReturn(['test']);

        $entry = new IncomingEntry([]);
        $entry->tags(['test']);

        $closure = $filter->apply();

        $this->assertTrue($closure($entry));
    }
}
