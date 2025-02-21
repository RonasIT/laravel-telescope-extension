<?php

namespace RonasIT\TelescopeExtension\Tests;

use Laravel\Telescope\EntryType;
use Laravel\Telescope\IncomingEntry;
use Laravel\Telescope\Watchers\RequestWatcher;
use RonasIT\TelescopeExtension\Filters\ProductionFilter;
use Mockery;
use Symfony\Component\HttpFoundation\Response;

class ProductionFilterTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config(['telescope.watchers.' . RequestWatcher::class => []]);
    }

    public function testDevEnv()
    {
        $this->mockEnvironment('development');

        $filter = new ProductionFilter();

        $closure = $filter->apply();

        $this->assertTrue($closure(new IncomingEntry([])));
    }

    public function testExceptionProdEnv()
    {
        $this->mockEnvironment('production');

        $filter = new ProductionFilter();

        $entry = Mockery::mock(IncomingEntry::class);
        $entry->type = EntryType::EXCEPTION;

        $entry->shouldReceive('isRequest')->andReturnFalse();
        $entry->shouldReceive('isClientRequest')->andReturnFalse();
        $entry->shouldReceive('isSlowQuery')->andReturnFalse();
        $entry->shouldReceive('isScheduledTask')->andReturnFalse();
        $entry->shouldReceive('hasMonitoredTag')->andReturnFalse();

        $closure = $filter->apply();

        $this->assertTrue($closure($entry));
    }

    public function testSuccessRequestProdEnv()
    {
        $this->mockEnvironment('production');

        $filter = new ProductionFilter();

        $entry = Mockery::mock(IncomingEntry::class);

        $entry->shouldReceive('isRequest')->andReturnTrue();
        $entry->shouldReceive('isClientRequest')->andReturnFalse();
        $entry->shouldReceive('isSlowQuery')->andReturnFalse();
        $entry->shouldReceive('isScheduledTask')->andReturnFalse();
        $entry->shouldReceive('hasMonitoredTag')->andReturnFalse();

        $closure = $filter->apply();

        $this->assertFalse($closure($entry));
    }

    public function testFailedRequestProdEnv()
    {
        $this->mockEnvironment('production');

        $filter = new ProductionFilter();

        $entry = Mockery::mock(IncomingEntry::class);
        $entry->content['response_status'] = Response::HTTP_BAD_REQUEST;

        $entry->shouldReceive('isRequest')->andReturnTrue();
        $entry->shouldReceive('isClientRequest')->andReturnFalse();
        $entry->shouldReceive('isSlowQuery')->andReturnFalse();
        $entry->shouldReceive('isScheduledTask')->andReturnFalse();
        $entry->shouldReceive('hasMonitoredTag')->andReturnFalse();

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

        $entry = Mockery::mock(IncomingEntry::class);
        $entry->type = EntryType::REQUEST;
        $entry->content['response_status'] = Response::HTTP_BAD_REQUEST;
        $entry->content['response']['message'] = 'ignore_message';

        $entry->shouldReceive('isRequest')->andReturnTrue();
        $entry->shouldReceive('isClientRequest')->andReturnFalse();
        $entry->shouldReceive('isSlowQuery')->andReturnFalse();
        $entry->shouldReceive('isScheduledTask')->andReturnFalse();
        $entry->shouldReceive('hasMonitoredTag')->andReturnFalse();

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

        $entry = Mockery::mock(IncomingEntry::class);
        $entry->type = EntryType::REQUEST;
        $entry->content['response_status'] = Response::HTTP_BAD_REQUEST;
        $entry->content['response']['message'] = 'ignore_message';

        $entry->shouldReceive('isRequest')->andReturnTrue();
        $entry->shouldReceive('isClientRequest')->andReturnFalse();
        $entry->shouldReceive('isSlowQuery')->andReturnFalse();
        $entry->shouldReceive('isScheduledTask')->andReturnFalse();
        $entry->shouldReceive('hasMonitoredTag')->andReturnFalse();

        $closure = $filter->apply();

        $this->assertTrue($closure($entry));
    }

    public function testSuccessClientRequestProdEnv()
    {
        $this->mockEnvironment('production');

        $filter = new ProductionFilter();

        $entry = Mockery::mock(IncomingEntry::class);

        $entry->shouldReceive('isRequest')->andReturnFalse();
        $entry->shouldReceive('isClientRequest')->andReturnTrue();
        $entry->shouldReceive('isSlowQuery')->andReturnFalse();
        $entry->shouldReceive('isScheduledTask')->andReturnFalse();
        $entry->shouldReceive('hasMonitoredTag')->andReturnFalse();

        $closure = $filter->apply();

        $this->assertFalse($closure($entry));
    }

    public function testFailedClientRequestProdEnv()
    {
        $this->mockEnvironment('production');

        $filter = new ProductionFilter();

        $entry = Mockery::mock(IncomingEntry::class);
        $entry->type = EntryType::CLIENT_REQUEST;
        $entry->content['response_status'] = Response::HTTP_BAD_REQUEST;

        $entry->shouldReceive('isRequest')->andReturnFalse();
        $entry->shouldReceive('isClientRequest')->andReturnTrue();
        $entry->shouldReceive('isSlowQuery')->andReturnFalse();
        $entry->shouldReceive('isScheduledTask')->andReturnFalse();
        $entry->shouldReceive('hasMonitoredTag')->andReturnFalse();

        $closure = $filter->apply();

        $this->assertTrue($closure($entry));
    }

    public function testSlowQueryProdEnv()
    {
        $this->mockEnvironment('production');

        $filter = new ProductionFilter();

        $entry = Mockery::mock(IncomingEntry::class);

        $entry->shouldReceive('isRequest')->andReturnFalse();
        $entry->shouldReceive('isClientRequest')->andReturnFalse();
        $entry->shouldReceive('isSlowQuery')->andReturnTrue();
        $entry->shouldReceive('isScheduledTask')->andReturnFalse();
        $entry->shouldReceive('hasMonitoredTag')->andReturnFalse();

        $closure = $filter->apply();

        $this->assertTrue($closure($entry));
    }

    public function testJobProdEnv()
    {
        $this->mockEnvironment('production');

        $filter = new ProductionFilter();

        $entry = Mockery::mock(IncomingEntry::class);
        $entry->type = EntryType::JOB;

        $entry->shouldReceive('isRequest')->andReturnFalse();
        $entry->shouldReceive('isClientRequest')->andReturnFalse();
        $entry->shouldReceive('isSlowQuery')->andReturnFalse();
        $entry->shouldReceive('isScheduledTask')->andReturnFalse();
        $entry->shouldReceive('hasMonitoredTag')->andReturnFalse();

        $closure = $filter->apply();

        $this->assertTrue($closure($entry));
    }

    public function testScheduledTaskProdEnv()
    {
        $this->mockEnvironment('production');

        $filter = new ProductionFilter();

        $entry = Mockery::mock(IncomingEntry::class);

        $entry->shouldReceive('isRequest')->andReturnFalse();
        $entry->shouldReceive('isClientRequest')->andReturnFalse();
        $entry->shouldReceive('isSlowQuery')->andReturnFalse();
        $entry->shouldReceive('isScheduledTask')->andReturnTrue();
        $entry->shouldReceive('hasMonitoredTag')->andReturnFalse();

        $closure = $filter->apply();

        $this->assertTrue($closure($entry));
    }

    public function testMonitoredTagProdEnv()
    {
        $this->mockEnvironment('production');

        config(['telescope.watchers.' . RequestWatcher::class => []]);

        $filter = new ProductionFilter();

        $entry = Mockery::mock(IncomingEntry::class);

        $entry->shouldReceive('isRequest')->andReturnFalse();
        $entry->shouldReceive('isClientRequest')->andReturnFalse();
        $entry->shouldReceive('isSlowQuery')->andReturnFalse();
        $entry->shouldReceive('isScheduledTask')->andReturnFalse();
        $entry->shouldReceive('hasMonitoredTag')->andReturnTrue();

        $closure = $filter->apply();

        $this->assertTrue($closure($entry));
    }
}
