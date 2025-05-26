<?php

namespace RonasIT\TelescopeExtension\Filters;

use Closure;
use Illuminate\Support\Facades\App;
use Laravel\Telescope\IncomingEntry;

class ProductionFilter extends AbstractFilter
{
    public function apply(): Closure
    {
        return fn (IncomingEntry $entry) => App::environment('local', 'development')
            || $this->isException($entry)
            || $this->isFailedRequest($entry)
            || $this->isFailedHttpRequest($entry)
            || $entry->isSlowQuery()
            || $this->isJob($entry)
            || $entry->isScheduledTask()
            || $entry->hasMonitoredTag();
    }
}
