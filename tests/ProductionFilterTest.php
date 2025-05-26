<?php

namespace RonasIT\TelescopeExtension\Tests;

use Laravel\Telescope\EntryType;
use Laravel\Telescope\IncomingEntry;
use Laravel\Telescope\Watchers\RequestWatcher;
use RonasIT\TelescopeExtension\Filters\ProductionFilter;
use RonasIT\TelescopeExtension\Tests\Support\Mock\IncomingClientRequest;
use RonasIT\TelescopeExtension\Tests\Support\Mock\IncomingRequest;
use RonasIT\TelescopeExtension\Tests\Support\ProductionFilterTestTrait;
use Symfony\Component\HttpFoundation\Response;

class ProductionFilterTest extends TestCase
{
    use ProductionFilterTestTrait;

    protected function setUp(): void
    {
        parent::setUp();

        config(['telescope.watchers.' . RequestWatcher::class => [
            'ignore_error_messages' => ['ignore_message'],
        ]]);
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

        $entry = new IncomingRequest();

        $closure = $filter->apply();

        $this->assertFalse($closure($entry));
    }

    public function testFailedRequestProdEnv()
    {
        $this->mockEnvironment('production');

        $filter = new ProductionFilter();

        $entry = new IncomingRequest(Response::HTTP_BAD_REQUEST);

        $closure = $filter->apply();

        $this->assertTrue($closure($entry));
    }

    public function testFailedRequestIgnoreMessageProdEnv()
    {
        $this->mockEnvironment('production');

        $filter = new ProductionFilter();

        $entry = new IncomingRequest(Response::HTTP_BAD_REQUEST, ['message' => 'ignore_message']);

        $closure = $filter->apply();

        $this->assertFalse($closure($entry));
    }

    public function testSuccessClientRequestProdEnv()
    {
        $this->mockEnvironment('production');

        $filter = new ProductionFilter();

        $entry = new IncomingClientRequest();

        $closure = $filter->apply();

        $this->assertFalse($closure($entry));
    }

    public function testFailedClientRequestProdEnv()
    {
        $this->mockEnvironment('production');

        $filter = new ProductionFilter();

        $entry = new IncomingClientRequest(Response::HTTP_BAD_REQUEST);

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
        $this->mockSelectTags(['tag' => 'test']);

        $this->mockEnvironment('production');

        $filter = new ProductionFilter();

        $entry = new IncomingEntry([]);
        $entry->tags(['test']);

        $closure = $filter->apply();

        $this->assertTrue($closure($entry));
    }
}
