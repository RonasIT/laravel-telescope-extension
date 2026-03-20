<?php

namespace RonasIT\TelescopeExtension\Tests;

use Illuminate\Foundation\Http\Events\RequestHandled;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Config;
use Laravel\Telescope\Telescope;
use RonasIT\TelescopeExtension\Watchers\RequestWatcher;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class RequestWatcherTest extends TestCase
{
    protected string $configName;

    protected RequestWatcher $requestWatcher;

    protected function setUp(): void
    {
        parent::setUp();

        Telescope::flushEntries();
        Telescope::$shouldRecord = true;

        $this->configName = 'telescope.' . RequestWatcher::class;

        Config::set("{$this->configName}.ignore_error_messages", [
            'Something went wrong!',
        ]);
    }

    protected function initWatcher(): void
    {
        $this->requestWatcher = new RequestWatcher(config($this->configName));
    }

    public function testIgnoreErrorMessage()
    {
        $this->initWatcher();

        $response = new Response(json_encode(['message' => 'Something went wrong!']), Response::HTTP_BAD_REQUEST);

        $event = new RequestHandled(new Request(), $response);

        $this->requestWatcher->recordRequest($event);

        $this->assertEmpty(Telescope::$entriesQueue);
    }

    public function testIgnoreErrorMessageAsException()
    {
        $this->initWatcher();

        $response = new Response();
        $response->exception = new UnprocessableEntityHttpException('Something went wrong!');

        $event = new RequestHandled(new Request(), $response);

        $this->requestWatcher->recordRequest($event);

        $this->assertEmpty(Telescope::$entriesQueue);
    }

    public function testNotIgnoreErrorMessage()
    {
        $this->initWatcher();

        $response = new Response(json_encode(['message' => 'Some other error']), Response::HTTP_BAD_REQUEST);

        $event = new RequestHandled(new Request(), $response);

        $this->requestWatcher->recordRequest($event);

        $this->assertNotEmpty(Telescope::$entriesQueue);
    }

    public function testNotIgnoreErrorMessageAsException()
    {
        $this->initWatcher();

        $response = new Response();
        $response->exception = new UnprocessableEntityHttpException('Some other error');

        $event = new RequestHandled(new Request(), $response);

        $this->requestWatcher->recordRequest($event);

        $this->assertNotEmpty(Telescope::$entriesQueue);
    }

    public function testMessageContent()
    {
        $this->initWatcher();

        $response = new Response('Something went wrong!', Response::HTTP_BAD_REQUEST);

        $event = new RequestHandled(new Request(), $response);

        $this->requestWatcher->recordRequest($event);

        $this->assertNotEmpty(Telescope::$entriesQueue);
    }

    public static function ignorePathsDataProvider(): array
    {
        return [
            'exact match: root path' => ['/'],
            'exact match: single segment' => ['/test'],
            'wildcard match: base path only' => ['/regex-suffix-test'],
            'wildcard match: base path with suffix' => ['/regex-suffix-test-123'],
            'wildcard match: base path with prefix' => ['test/regex-prefix-test'],
        ];
    }

    /**
     * @dataProvider ignorePathsDataProvider
     */
    public function testIgnorePath(string $path): void
    {
        Config::set("{$this->configName}.ignore_paths", [
            '/',
            'test',
            'regex-suffix-test*',
            '*regex-prefix-test',
        ]);

        $this->initWatcher();

        $event = new RequestHandled(Request::create($path), new Response());

        $this->requestWatcher->recordRequest($event);

        $this->assertEmpty(Telescope::$entriesQueue);
    }

    public static function notIgnorePathsDataProvider(): array
    {
        return [
            'unrelated path' => ['/other'],
            'exact match does not cover subpaths' => ['/test/nested'],
            'similar but not matching wildcard' => ['/not-regex-test'],
        ];
    }

    /**
     * @dataProvider notIgnorePathsDataProvider
     */
    public function testNotIgnorePath(string $path): void
    {
        Config::set("{$this->configName}.ignore_paths", [
            'test',
            'regex-test*',
        ]);

        $this->initWatcher();

        $event = new RequestHandled(Request::create($path), new Response());

        $this->requestWatcher->recordRequest($event);

        $this->assertNotEmpty(Telescope::$entriesQueue);
    }

    public function testSymfonyResponse()
    {
        $this->initWatcher();

        $event = new RequestHandled(new Request(), new SymfonyResponse());

        $this->requestWatcher->recordRequest($event);

        $this->assertNotEmpty(Telescope::$entriesQueue);
    }

    public function testIgnoreErrorMessageSymfonyResponse()
    {
        $this->initWatcher();

        $response = new SymfonyResponse(json_encode(['message' => 'Something went wrong!']), SymfonyResponse::HTTP_BAD_REQUEST);

        $event = new RequestHandled(new Request(), $response);

        $this->requestWatcher->recordRequest($event);

        $this->assertEmpty(Telescope::$entriesQueue);
    }
}
