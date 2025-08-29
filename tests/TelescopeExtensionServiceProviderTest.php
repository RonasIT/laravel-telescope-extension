<?php

use RonasIT\TelescopeExtension\Tests\TestCase;
use Illuminate\Support\Facades\Config;
use RonasIT\TelescopeExtension\TelescopeExtensionServiceProvider;
use RonasIT\Support\Http\Middleware\CheckIpMiddleware;

class TelescopeExtensionServiceProviderTest extends TestCase
{
    protected array $initialMiddlewares = [
        'first.middleware',
        'second.middleware',
    ];

    protected function setUp(): void
    {
        parent::setUp();

        Config::set('telescope.allowed_ips', ['127.0.0.1', '127.0.0.2']);
        Config::set('telescope.middleware', $this->initialMiddlewares);
    }

    public function testAddCheckIpMiddleware()
    {
        new TelescopeExtensionServiceProvider($this->app)->register();

        $expectedMiddlewares = [
            ...$this->initialMiddlewares,
            CheckIpMiddleware::class . ':127.0.0.1,127.0.0.2',
        ];

        $this->assertEquals($expectedMiddlewares, Config::get('telescope.middleware'));
    }

    public function testCheckIpMiddlewareNotAdded()
    {
        Config::set('telescope.allowed_ips', []);

        new TelescopeExtensionServiceProvider($this->app)->register();

        $this->assertEquals($this->initialMiddlewares, Config::get('telescope.middleware'));
    }
}
