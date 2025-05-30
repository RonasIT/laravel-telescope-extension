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
use Closure;

class ProductionFilterTest extends TestCase
{
    use ProductionFilterTestTrait;

    protected ProductionFilter $filter;

    protected Closure $closure;

    protected function setUp(): void
    {
        parent::setUp();

        config(['telescope.watchers.' . RequestWatcher::class => [
            'ignore_error_messages' => ['ignore_message'],
        ]]);

        $this->filter = new ProductionFilter();

        $this->closure = call_user_func($this->filter);
    }

    public function testDevEnv()
    {
        $this->mockEnvironment('development');

        $result = call_user_func($this->closure, new IncomingEntry([]));

        $this->assertTrue($result);
    }

    public function testLocalEnv()
    {
        $this->mockEnvironment('local');

        $result = call_user_func($this->closure, new IncomingEntry([]));

        $this->assertTrue($result);
    }

    public function testExceptionProdEnv()
    {
        $this->mockEnvironment('production');

        $entry = new IncomingEntry([]);
        $entry->type(EntryType::EXCEPTION);

        $result = call_user_func($this->closure, $entry);

        $this->assertTrue($result);
    }

    public function testSuccessRequestProdEnv()
    {
        $this->mockEnvironment('production');

        $entry = new IncomingRequest();

        $result = call_user_func($this->closure, $entry);

        $this->assertFalse($result);
    }

    public function testFailedRequestProdEnv()
    {
        $this->mockEnvironment('production');

        $entry = new IncomingRequest(Response::HTTP_BAD_REQUEST);

        $result = call_user_func($this->closure, $entry);

        $this->assertTrue($result);
    }

    public function testFailedRequestIgnoreMessageProdEnv()
    {
        $this->mockEnvironment('production');

        $entry = new IncomingRequest(Response::HTTP_BAD_REQUEST, ['message' => 'ignore_message']);

        $result = call_user_func($this->closure, $entry);

        $this->assertFalse($result);
    }

    public function testSuccessClientRequestProdEnv()
    {
        $this->mockEnvironment('production');

        $entry = new IncomingClientRequest();

        $result = call_user_func($this->closure, $entry);

        $this->assertFalse($result);
    }

    public function testFailedClientRequestProdEnv()
    {
        $this->mockEnvironment('production');

        $entry = new IncomingClientRequest(Response::HTTP_BAD_REQUEST);

        $result = call_user_func($this->closure, $entry);

        $this->assertTrue($result);
    }

    public function testSlowQueryProdEnv()
    {
        $this->mockEnvironment('production');

        $entry = new IncomingEntry(['slow' => true]);
        $entry->type(EntryType::QUERY);

        $result = call_user_func($this->closure, $entry);

        $this->assertTrue($result);
    }

    public function testJobProdEnv()
    {
        $this->mockEnvironment('production');

        $entry = new IncomingEntry([]);
        $entry->type(EntryType::JOB);

        $result = call_user_func($this->closure, $entry);

        $this->assertTrue($result);
    }

    public function testScheduledTaskProdEnv()
    {
        $this->mockEnvironment('production');

        $entry = new IncomingEntry([]);
        $entry->type(EntryType::SCHEDULED_TASK);

        $result = call_user_func($this->closure, $entry);

        $this->assertTrue($result);
    }

    public function testMonitoredTagProdEnv()
    {
        $this->mockSelectTags(['tag' => 'test']);

        $this->mockEnvironment('production');

        $entry = new IncomingEntry([]);
        $entry->tags(['test']);

        $result = call_user_func($this->closure, $entry);

        $this->assertTrue($result);
    }
}
