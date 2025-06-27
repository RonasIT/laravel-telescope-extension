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

        Telescope::$shouldRecord = true;

        $this->configName = 'telescope.' . RequestWatcher::class;
    }

    public function testIgnoreErrorMessage()
    {
        Config::set("{$this->configName}.ignore_error_messages", [
            'Something went wrong!',
        ]);

        $response = new Response();
        $response->exception = new UnprocessableEntityHttpException('Something went wrong!');

        $event = new RequestHandled(new Request(), $response);

        $options = config($this->configName);

        (new RequestWatcher($options))->recordRequest($event);

        $this->assertEmpty(Telescope::$entriesQueue);
    }

    public function testNotIgnoreErrorMessage()
    {
        Config::set("{$this->configName}.ignore_error_messages", [
            'Something went wrong',
            'Internal error',
        ]);

        $response = new Response();
        $response->exception = new UnprocessableEntityHttpException('Some other error');

        $event = new RequestHandled(new Request(), $response);

        $options = config($this->configName);

        (new RequestWatcher($options))->recordRequest($event);

        $this->assertNotEmpty(Telescope::$entriesQueue);
    }
}
