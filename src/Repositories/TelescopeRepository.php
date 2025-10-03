<?php

namespace RonasIT\TelescopeExtension\Repositories;

use DateTimeInterface;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Laravel\Telescope\EntryType;
use Laravel\Telescope\Storage\DatabaseEntriesRepository;
use RonasIT\TelescopeExtension\Console\Commands\TelescopePrune;
use RonasIT\TelescopeExtension\Traits\TelescopeTrait;

class TelescopeRepository extends DatabaseEntriesRepository
{
    use TelescopeTrait;

    protected array $pruneTypes = [];

    public function prune(DateTimeInterface $before, $keepExceptions = null): int
    {
        $query = $this
            ->table('telescope_entries')
            ->where('created_at', '<', $before)
            ->where(function (Builder $subQuery) {
                foreach ($this->pruneTypes as $type) {
                    if (in_array($type, TelescopePrune::EXCEPTION_TYPES)) {
                        $subQuery->orWhere(function ($subSubQuery) use ($type) {
                            $subSubQuery
                                ->where('type', EntryType::EXCEPTION)
                                ->whereRaw(
                                    ($type === TelescopePrune::UNRESOLVED_EXCEPTION)
                                        ? "content::jsonb->>'resolved_at' is null"
                                        : "content::jsonb->>'resolved_at' is not null"
                                );
                        });
                    } elseif ($type === TelescopePrune::COMPLETED_JOB_TYPE) {
                        $subQuery->orWhere(fn ($subSubQuery) => $subSubQuery
                            ->where('type', EntryType::JOB)
                            ->whereRaw("content::jsonb->>'status' = 'processed'")
                        );
                    } else {
                        $subQuery->orWhere('type', $type);
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

    public function store(Collection $entries): void
    {
        if ($entries->isEmpty()) {
            return;
        }

        [$exceptions, $entries] = $entries->partition->isException();

        $this->storeExceptions($exceptions);

        $table = $this->table('telescope_entries');

        $entries->chunk($this->chunkSize)->each(function ($chunked) use ($table) {
            $table->insert($chunked->map(function ($entry) {
                $content = json_encode($entry->content, JSON_INVALID_UTF8_SUBSTITUTE);

                if ($this->isPostgreDatabaseDriver()) {
                    $content = Str::remove(['\u0000*', '\u0000'], $content);
                }

                $entry->content = $content;

                return $entry->toArray();
            })->toArray());
        });

        $this->storeTags($entries->pluck('tags', 'uuid'));
    }

    public function countByType(string $type): int
    {
        return $this->table('telescope_entries')->where('type', $type)->count();
    }

    public function getReportableEntriesCountMap(): Collection
    {
        return $this
            ->table('telescope_entries')
            ->select(DB::raw('type, count(*) as count'))
            ->where(function (Builder $subQuery) {
                $subQuery
                    ->whereNotIn('type', [EntryType::EXCEPTION, EntryType::JOB])
                    ->orWhere(function (Builder $subQuery) {
                        $subQuery
                            ->where('type', EntryType::EXCEPTION)
                            ->whereNull('content->resolved_at');
                    })
                    ->orWhere(function (Builder $subQuery) {
                        $subQuery
                            ->where('type', EntryType::JOB)
                            ->where('content->status', 'failed');
                    });
            })
            ->groupBy('type')
            ->pluck('count', 'type');
    }
}
