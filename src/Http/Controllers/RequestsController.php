<?php

namespace RonasIT\TelescopeExtension\Http\Controllers;

use Laravel\Telescope\Watchers\RequestWatcher;
use Laravel\Telescope\Http\Controllers\RequestsController as BaseController;

class RequestsController extends BaseController
{
    protected function watcher(): string
    {
        return RequestWatcher::class;
    }
}
