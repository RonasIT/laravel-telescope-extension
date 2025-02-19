<?php

namespace RonasIT\TelescopeExtension\Filters;

use Closure;
use Laravel\Telescope\IncomingEntry;

class PRODFilter extends AbstractFilter
{
    public function apply(): Closure
    {
        return fn (IncomingEntry $entry) => $this->isException($entry)
            || $this->isFailedRequest($entry)
            || $this->isFailedHttpRequest($entry)
            || $entry->isSlowQuery()
            || $this->isJob($entry)
            || $entry->isScheduledTask()
            || $entry->hasMonitoredTag();
    }
}
