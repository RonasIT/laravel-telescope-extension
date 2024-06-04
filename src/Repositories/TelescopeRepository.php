<?php

namespace RonasIT\TelescopeExtension\Repositories;

use DateTimeInterface;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Arr;
use Laravel\Telescope\EntryType;
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
            ->where(function(Builder $subQuery) {
                foreach ($this->pruneTypes as $type) {
                    if (in_array($type, TelescopePrune::EXCEPTION_TYPES)) {
                        $subQuery->orWhereRaw(
                            ($type === TelescopePrune::UNRESOLVED_EXCEPTION)
                                ? "content::jsonb->>'resolved_at' is null"
                                : "content::jsonb->>'resolved_at' is not null"
                        );
                    } else {
                        $subQuery->orWhere('type', $this->pruneTypes);
                    }
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
