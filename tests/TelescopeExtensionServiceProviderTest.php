<?php

use RonasIT\TelescopeExtension\Tests\TestCase;
use Illuminate\Support\Facades\Config;
use RonasIT\TelescopeExtension\TelescopeExtensionServiceProvider;
use RonasIT\Support\Http\Middleware\CheckIpMiddleware;

class TelescopeExtensionServiceProviderTest extends TestCase
{
    public function testAddCheckIpMiddleware()
    {
        Config::set('telescope.allowed_ips', ['127.0.0.1', '127.0.0.2']);
        Config::set('telescope.middleware', ['first.middleware', 'second.middleware']);

        $provider = new TelescopeExtensionServiceProvider($this->app);
        $provider->boot();

        $expectedMiddleware = [
            'first.middleware',
            'second.middleware',
            CheckIpMiddleware::class . ':127.0.0.1,127.0.0.2',
        ];

        $this->assertEquals($expectedMiddleware, Config::get('telescope.middleware'));
    }

    public function testCheckIpMiddlewareNotAdded()
    {
        Config::set('telescope.allowed_ips', []);
        Config::set('telescope.middleware', ['first.middleware', 'second.middleware']);

        $provider = new TelescopeExtensionServiceProvider($this->app);
        $provider->boot();

        $expectedMiddleware = [
            'first.middleware',
            'second.middleware',
        ];

        $this->assertEquals($expectedMiddleware, Config::get('telescope.middleware'));
    }
}
