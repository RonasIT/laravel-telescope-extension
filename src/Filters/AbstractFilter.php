<?php

namespace RonasIT\TelescopeExtension\Filters;

use Closure;
use Laravel\Telescope\EntryType;
use Laravel\Telescope\IncomingEntry;
use Symfony\Component\HttpFoundation\Response;

abstract class AbstractFilter
{
    public abstract function __invoke(): Closure;

    protected function isException(IncomingEntry $entry): bool
    {
        return $entry->type === EntryType::EXCEPTION;
    }

    protected function isFailedRequest(IncomingEntry $entry): bool
    {
        return $entry->isRequest()
            && $this->hasFailedStatus($entry);
    }

    protected function isFailedHttpRequest(IncomingEntry $entry): bool
    {
        return $entry->isClientRequest() && $this->hasFailedStatus($entry);
    }

    protected function isJob(IncomingEntry $entry): bool
    {
        return $entry->type === EntryType::JOB;
    }

    protected function hasFailedStatus(IncomingEntry $entry): bool
    {
        $currentStatus = $entry->content['response_status'] ?? Response::HTTP_OK;

        return $currentStatus >= Response::HTTP_BAD_REQUEST;
    }
}
