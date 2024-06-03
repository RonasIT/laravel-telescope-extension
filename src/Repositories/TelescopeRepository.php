<?php

namespace RonasIT\TelescopeExtension\Repositories;

use DateTimeInterface;
use Illuminate\Database\Query\Builder;
use Laravel\Telescope\Storage\DatabaseEntriesRepository;
use RonasIT\TelescopeExtension\Console\Commands\TelescopePrune;

class TelescopeRepository extends DatabaseEntriesRepository
{
    protected array $pruneTypes;

    public function prune(DateTimeInterface $before): int
    {
        $query = $this
            ->table('telescope_entries')
            ->where('created_at', '<', $before)
            ->when($this->pruneTypes, function(Builder $subQuery) {
                if ($this->pruneTypes === TelescopePrune::UNRESOLVED_EXCEPTION) {
                    return $subQuery
                        ->where('type', 'exception')
                        ->whereRaw("content::jsonb->>'resolved_at' is null");
                } else {
                    return $subQuery->whereIn('type', $this->pruneTypes);
                }
            });

        $totalDeleted = 0;

        do {
            $deleted = $query->take($this->chunkSize)->delete();

            $totalDeleted += $deleted;
        } while ($deleted !== 0);

        return $totalDeleted;
    }

    public function pruneByEventType(array $types, DateTimeInterface $before): int
    {
        $this->pruneTypes = $types;

        return $this->prune($before);
    }

    public function clear(): void
    {
        $this->table('telescope_entries')->delete();
        $this->table('telescope_monitoring')->delete();
    }
}
