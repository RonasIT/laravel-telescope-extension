<?php

namespace RonasIT\TelescopeExtension\Filters;

use Closure;
use Illuminate\Support\Arr;
use Laravel\Telescope\EntryType;
use Laravel\Telescope\IncomingEntry;
use Laravel\Telescope\Watchers\RequestWatcher;
use Symfony\Component\HttpFoundation\Response;

abstract class AbstractFilter
{
    protected readonly array $requestWatcherConfig;

    public function __construct()
    {
        $this->requestWatcherConfig = config('telescope.watchers.' . RequestWatcher::class);
    }

    public abstract function __invoke(): Closure;

    protected function isException(IncomingEntry $entry): bool
    {
        return $entry->type === EntryType::EXCEPTION;
    }

    protected function isFailedRequest(IncomingEntry $entry): bool
    {
        return $entry->isRequest()
            && $this->hasFailedStatus($entry)
            && !$this->hasIgnorableMessage($entry->content);
    }

    protected function hasIgnorableMessage(array $content): bool
    {
        $errorMessage = Arr::get($content, 'response.message');
        $ignorableMessages = Arr::get($this->requestWatcherConfig, 'ignore_error_messages', []);

        return in_array($errorMessage, $ignorableMessages);
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
