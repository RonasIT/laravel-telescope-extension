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
        $shouldSkip = !Telescope::isRecording()
            || $this->shouldIgnorePath($event)
            || $this->shouldIgnoreErrorMessage($event);

        if (!$shouldSkip) {
            parent::recordRequest($event);
        }
    }

    protected function shouldIgnoreErrorMessage(RequestHandled $event): bool
    {
        $message = property_exists($event->response, 'exception')
            ? $event->response->exception?->getMessage()
            : null;

        if (empty($message)) {
            $responseContent = json_decode($event->response->getContent(), true);

            $message = Arr::get($responseContent, 'message');
        }

        return in_array($message, Arr::get($this->options, 'ignore_error_messages', []));
    }

    protected function shouldIgnorePath(RequestHandled $event): bool
    {
        return in_array($event->request->path(), Arr::get($this->options, 'ignore_paths', []));
    }
}
