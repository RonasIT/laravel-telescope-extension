<?php

namespace RonasIT\TelescopeExtension\Tests;

use Illuminate\Foundation\Http\Events\RequestHandled;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Config;
use Laravel\Telescope\Telescope;
use RonasIT\TelescopeExtension\Watchers\RequestWatcher;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class RequestWatcherTest extends TestCase
{
    protected string $configName;

    protected function setUp(): void
    {
        parent::setUp();

        Telescope::flushEntries();
        Telescope::$shouldRecord = true;

        $this->configName = 'telescope.' . RequestWatcher::class;

        Config::set("{$this->configName}.ignore_error_messages", [
            'Something went wrong!',
        ]);

        Config::set("{$this->configName}.ignore_paths", [
            'test',
        ]);
    }

    public function testIgnoreErrorMessage()
    {
        $response = new Response(json_encode(['message' => 'Something went wrong!']), Response::HTTP_BAD_REQUEST);

        $event = new RequestHandled(new Request(), $response);

        $options = config($this->configName);

        new RequestWatcher($options)->recordRequest($event);

        $this->assertEmpty(Telescope::$entriesQueue);
    }

    public function testIgnoreErrorMessageAsException()
    {
        $response = new Response();
        $response->exception = new UnprocessableEntityHttpException('Something went wrong!');

        $event = new RequestHandled(new Request(), $response);

        $options = config($this->configName);

        new RequestWatcher($options)->recordRequest($event);

        $this->assertEmpty(Telescope::$entriesQueue);
    }

    public function testNotIgnoreErrorMessage()
    {
        $response = new Response(json_encode(['message' => 'Some other error']), Response::HTTP_BAD_REQUEST);

        $event = new RequestHandled(new Request(), $response);

        $options = config($this->configName);

        new RequestWatcher($options)->recordRequest($event);

        $this->assertNotEmpty(Telescope::$entriesQueue);
    }

    public function testNotIgnoreErrorMessageAsException()
    {
        $response = new Response();
        $response->exception = new UnprocessableEntityHttpException('Some other error');

        $event = new RequestHandled(new Request(), $response);

        $options = config($this->configName);

        new RequestWatcher($options)->recordRequest($event);

        $this->assertNotEmpty(Telescope::$entriesQueue);
    }

    public function testMessageContent()
    {
        $response = new Response('Something went wrong!', Response::HTTP_BAD_REQUEST);

        $event = new RequestHandled(new Request(), $response);

        $options = config($this->configName);

        new RequestWatcher($options)->recordRequest($event);

        $this->assertNotEmpty(Telescope::$entriesQueue);
    }

    public function testIgnorePath()
    {
        $event = new RequestHandled(Request::create('/test'), new Response());

        $options = config($this->configName);

        new RequestWatcher($options)->recordRequest($event);

        $this->assertEmpty(Telescope::$entriesQueue);
    }

    public function testIgnorePathAnotherPath()
    {
        $event = new RequestHandled(Request::create('/test/test'), new Response());

        $options = config($this->configName);

        new RequestWatcher($options)->recordRequest($event);

        $this->assertNotEmpty(Telescope::$entriesQueue);
    }
}
