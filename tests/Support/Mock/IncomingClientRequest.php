<?php

namespace RonasIT\TelescopeExtension\Tests\Support\Mock;

use Laravel\Telescope\EntryType;

class IncomingClientRequest extends IncomingRequest
{
    public function __construct(?int $status = null, array $responseData = [])
    {
        parent::__construct($status, $responseData);

        $this->type(EntryType::CLIENT_REQUEST);
    }
}
