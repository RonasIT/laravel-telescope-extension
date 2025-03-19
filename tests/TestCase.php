<?php

namespace RonasIT\TelescopeExtension\Tests;

use Orchestra\Testbench\TestCase as BaseTestCase;

class TestCase extends BaseTestCase
{
    protected function mockEnvironment(string $environment): void
    {
        $this->app->detectEnvironment(fn () => $environment);
    }
}
