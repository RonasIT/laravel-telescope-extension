<?php

namespace RonasIT\TelescopeExtension\Tests\Support\Mock;

use Laravel\Telescope\EntryType;
use Laravel\Telescope\IncomingEntry;

class IncomingRequest extends IncomingEntry
{
    public function __construct(?int $status = null, array $responseData = [])
    {
        parent::__construct([
            'response_status' => $status,
            'response' => $responseData,
        ]);

        $this->type(EntryType::REQUEST);
    }
}