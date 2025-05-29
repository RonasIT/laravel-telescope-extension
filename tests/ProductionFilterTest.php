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

    protected ProductionFilter $filter;

    protected function setUp(): void
    {
        parent::setUp();

        config(['telescope.watchers.' . RequestWatcher::class => [
            'ignore_error_messages' => ['ignore_message'],
        ]]);

        $this->filter = new ProductionFilter();
    }

    public function testDevEnv()
    {
        $this->mockEnvironment('development');

        $closure = ($this->filter)();

        $this->assertTrue($closure(new IncomingEntry([])));
    }

    public function testLocalEnv()
    {
        $this->mockEnvironment('local');

        $closure = ($this->filter)();

        $this->assertTrue($closure(new IncomingEntry([])));
    }

    public function testExceptionProdEnv()
    {
        $this->mockEnvironment('production');

        $entry = new IncomingEntry([]);
        $entry->type(EntryType::EXCEPTION);

        $closure = ($this->filter)();

        $this->assertTrue($closure($entry));
    }

    public function testSuccessRequestProdEnv()
    {
        $this->mockEnvironment('production');

        $entry = new IncomingRequest();

        $closure = ($this->filter)();

        $this->assertFalse($closure($entry));
    }

    public function testFailedRequestProdEnv()
    {
        $this->mockEnvironment('production');

        $entry = new IncomingRequest(Response::HTTP_BAD_REQUEST);

        $closure = ($this->filter)();

        $this->assertTrue($closure($entry));
    }

    public function testFailedRequestIgnoreMessageProdEnv()
    {
        $this->mockEnvironment('production');

        $entry = new IncomingRequest(Response::HTTP_BAD_REQUEST, ['message' => 'ignore_message']);

        $closure = ($this->filter)();

        $this->assertFalse($closure($entry));
    }

    public function testSuccessClientRequestProdEnv()
    {
        $this->mockEnvironment('production');

        $entry = new IncomingClientRequest();

        $closure = ($this->filter)();

        $this->assertFalse($closure($entry));
    }

    public function testFailedClientRequestProdEnv()
    {
        $this->mockEnvironment('production');

        $entry = new IncomingClientRequest(Response::HTTP_BAD_REQUEST);

        $closure = ($this->filter)();

        $this->assertTrue($closure($entry));
    }

    public function testSlowQueryProdEnv()
    {
        $this->mockEnvironment('production');

        $entry = new IncomingEntry(['slow' => true]);
        $entry->type(EntryType::QUERY);

        $closure = ($this->filter)();

        $this->assertTrue($closure($entry));
    }

    public function testJobProdEnv()
    {
        $this->mockEnvironment('production');

        $entry = new IncomingEntry([]);
        $entry->type(EntryType::JOB);

        $closure = ($this->filter)();

        $this->assertTrue($closure($entry));
    }

    public function testScheduledTaskProdEnv()
    {
        $this->mockEnvironment('production');

        $entry = new IncomingEntry([]);
        $entry->type(EntryType::SCHEDULED_TASK);

        $closure = ($this->filter)();

        $this->assertTrue($closure($entry));
    }

    public function testMonitoredTagProdEnv()
    {
        $this->mockSelectTags(['tag' => 'test']);

        $this->mockEnvironment('production');

        $entry = new IncomingEntry([]);
        $entry->tags(['test']);

        $closure = ($this->filter)();

        $this->assertTrue($closure($entry));
    }
}
