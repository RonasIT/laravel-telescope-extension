<?php

namespace RonasIT\TelescopeExtension\Http\Controllers;

use Laravel\Telescope\Http\Controllers\RequestsController as BaseController;
use RonasIT\TelescopeExtension\Watchers\RequestWatcher;

class RequestsController extends BaseController
{
    protected function watcher(): string
    {
        return RequestWatcher::class;
    }
}
