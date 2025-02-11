<?php

namespace RonasIT\TelescopeExtension\Traits;

trait TelescopeTrait
{
    public function getDatabaseDriver(): ?string
    {
        $connection = config('telescope.storage.database.connection');

        return config("database.connections.{$connection}.driver");
    }
}
