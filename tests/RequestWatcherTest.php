<?php

namespace RonasIT\TelescopeExtension\Tests;

use Illuminate\Foundation\Http\Events\RequestHandled;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Config;
use Laravel\Telescope\Telescope;
use PHPUnit\Framework\Attributes\DataProvider;
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

        $this->requestWatcher = new RequestWatcher(config($this->configName));
    }

    public function testIgnoreErrorMessage()
    {
        $response = new Response(json_encode(['message' => 'Something went wrong!']), Response::HTTP_BAD_REQUEST);

        $event = new RequestHandled(new Request(), $response);

        $this->requestWatcher->recordRequest($event);

        $this->assertEmpty(Telescope::$entriesQueue);
    }

    public function testIgnoreErrorMessageAsException()
    {
        $response = new Response();
        $response->exception = new UnprocessableEntityHttpException('Something went wrong!');

        $event = new RequestHandled(new Request(), $response);

        $this->requestWatcher->recordRequest($event);

        $this->assertEmpty(Telescope::$entriesQueue);
    }

    public function testNotIgnoreErrorMessage()
    {
        $response = new Response(json_encode(['message' => 'Some other error']), Response::HTTP_BAD_REQUEST);

        $event = new RequestHandled(new Request(), $response);

        $this->requestWatcher->recordRequest($event);

        $this->assertNotEmpty(Telescope::$entriesQueue);
    }

    public function testNotIgnoreErrorMessageAsException()
    {
        $response = new Response();
        $response->exception = new UnprocessableEntityHttpException('Some other error');

        $event = new RequestHandled(new Request(), $response);

        $this->requestWatcher->recordRequest($event);

        $this->assertNotEmpty(Telescope::$entriesQueue);
    }

    public function testMessageContent()
    {
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
            'trailing wildcard: exact base' => ['/suffix-test'],
            'leading wildcard: exact base' => ['/prefix-test'],
            'trailing wildcard: base with suffix' => ['/suffix-test-123'],
            'leading wildcard: base with prefix' => ['/123-prefix-test'],
        ];
    }

    #[DataProvider('ignorePathsDataProvider')]
    public function testIgnorePath(string $path): void
    {
        Config::set("{$this->configName}.ignore_paths", [
            '/',
            'test',
            'suffix-test*',
            '*prefix-test',
        ]);

        $requestWatcher = new RequestWatcher(config($this->configName));

        $event = new RequestHandled(Request::create($path), new Response());

        $requestWatcher->recordRequest($event);

        $this->assertEmpty(Telescope::$entriesQueue);
    }

    public static function notIgnorePathsDataProvider(): array
    {
        return [
            'unrelated path' => ['/other'],
            'exact match does not cover subpaths' => ['/test/nested'],
            'case sensitive: uppercase path does not match lowercase pattern' => ['/TEST'],
            'wildcard: does not match wildcard pattern' => ['/123-wildcard-123'],
        ];
    }

    #[DataProvider('notIgnorePathsDataProvider')]
    public function testNotIgnorePath(string $path): void
    {
        Config::set("{$this->configName}.ignore_paths", [
            'test',
            'wildcard-test*',
        ]);

        $requestWatcher = new RequestWatcher(config($this->configName));

        $event = new RequestHandled(Request::create($path), new Response());

        $requestWatcher->recordRequest($event);

        $this->assertNotEmpty(Telescope::$entriesQueue);
    }

    public function testSymfonyResponse()
    {
        $event = new RequestHandled(new Request(), new SymfonyResponse());

        $this->requestWatcher->recordRequest($event);

        $this->assertNotEmpty(Telescope::$entriesQueue);
    }

    public function testIgnoreErrorMessageSymfonyResponse()
    {
        $response = new SymfonyResponse(json_encode(['message' => 'Something went wrong!']), SymfonyResponse::HTTP_BAD_REQUEST);

        $event = new RequestHandled(new Request(), $response);

        $this->requestWatcher->recordRequest($event);

        $this->assertEmpty(Telescope::$entriesQueue);
    }
}
