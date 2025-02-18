<?php

namespace RonasIT\TelescopeExtension\Filters;

use Closure;
use Laravel\Telescope\EntryType;
use Laravel\Telescope\IncomingEntry;
use Laravel\Telescope\Watchers\RequestWatcher;
use Symfony\Component\HttpFoundation\Response;

class TelescopeFilter
{
    protected readonly array $requestWatcherConfig;

    public function __construct()
    {
        $this->requestWatcherConfig = config('telescope.watchers.' . RequestWatcher::class);
    }

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

    protected function isException(IncomingEntry $entry): bool
    {
        return $entry->type === EntryType::EXCEPTION;
    }

    protected function isFailedRequest(IncomingEntry $entry): bool
    {
        $currentStatus = $entry->content['response_status'] ?? Response::HTTP_OK;

        return $entry->isRequest()
            && ($entry->content['duration'] > $this->requestWatcherConfig['max_duration'] * 1000 || $currentStatus >= Response::HTTP_BAD_REQUEST);
    }

    protected function isFailedHttpRequest(IncomingEntry $entry): bool
    {
        return $entry->type === EntryType::CLIENT_REQUEST
            && ($entry->content['response_status'] ?? Response::HTTP_OK) >= Response::HTTP_BAD_REQUEST;
    }

    protected function isJob(IncomingEntry $entry): bool
    {
        return $entry->type === EntryType::JOB;
    }
}
