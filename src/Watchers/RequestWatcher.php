<?php

namespace RonasIT\TelescopeExtension\Watchers;

use Illuminate\Foundation\Http\Events\RequestHandled;
use Illuminate\Support\Arr;
use Laravel\Telescope\Telescope;
use Laravel\Telescope\Watchers\RequestWatcher as BaseRequestWatcher;

class RequestWatcher extends BaseRequestWatcher
{
    public function recordRequest(RequestHandled $event): void
    {
        $shouldSkip = !Telescope::isRecording() || $this->shouldIgnoreErrorMessage($event);

        if (!$shouldSkip) {
            parent::recordRequest($event);
        }
    }

    protected function shouldIgnoreErrorMessage(RequestHandled $event): bool
    {
        $message = $event->response->exception?->getMessage();

        return in_array($message, Arr::get($this->options, 'ignore_error_messages', []));
    }
}
