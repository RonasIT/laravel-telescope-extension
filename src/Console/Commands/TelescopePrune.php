<?php

namespace RonasIT\TelescopeExtension\Console\Commands;

use RonasIT\TelescopeExtension\Repositories\TelescopeRepository;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Laravel\Telescope\EntryType;
use Exception;

class TelescopePrune extends Command
{
    const UNRESOLVED_EXCEPTION = 'unresolved_exception';
    const RESOLVED_EXCEPTION = 'resolved_exception';
    const COMPLETED_JOB_TYPE = 'completed_job';

    const COMMON_TYPES = [
        EntryType::BATCH,
        EntryType::CACHE,
        EntryType::DUMP,
        EntryType::EVENT,
        EntryType::EXCEPTION,
        EntryType::JOB,
        EntryType::LOG,
        EntryType::MAIL,
        EntryType::MODEL,
        EntryType::NOTIFICATION,
        EntryType::QUERY,
        EntryType::REDIS,
        EntryType::REQUEST,
        EntryType::SCHEDULED_TASK,
        EntryType::GATE,
        EntryType::VIEW,
        self::COMPLETED_JOB_TYPE,
    ];

    const EXCEPTION_TYPES = [
        self::UNRESOLVED_EXCEPTION,
        self::RESOLVED_EXCEPTION,
    ];

    const TYPES = [
        ...self::COMMON_TYPES,
        ...self::EXCEPTION_TYPES,
    ];

    protected $signature = 'telescope:prune
                            {--set-hours= : description}
                            {--hours= : description}';

    protected $description = 'Command description';

    protected array $expirationHours = [];

    protected int $defaultExpirationHours;

    public function handle(): void
    {
        $this->defaultExpirationHours = 0;

        $this->validateSetHoursOption();
        $this->validateHoursOption();
        $this->pruneSetHours();
        $this->pruneHours();
    }

    protected function validateSetHoursOption(): void
    {
        $values = $this->option('set-hours');

        if ($values) {
            $typeHours = explode(',', $values);

            foreach ($typeHours as $typeHour) {
                $parts = explode(':', $typeHour);

                if (count($parts) !== 2) {
                    throw new Exception("Incorrect value '{$typeHour}' of the 'set-hours' option.");
                }

                [$type, $hours] = $parts;

                if (!in_array($type, self::TYPES)) {
                    throw new Exception("Incorrect type value '{$type}'.");
                }

                if (!$hours) {
                    throw new Exception("Hours value for '{$type}' type must be set.");
                }

                if (!is_numeric($hours)) {
                    throw new Exception("Hours value for '{$type}' type must be a number.");
                }

                $this->expirationHours[$type] = $hours;
            }
        }
    }

    protected function validateHoursOption(): void
    {
        $hours = $this->option('hours');

        if ($hours) {
            if (!is_numeric($hours)) {
                throw new Exception('Hours hours must be a number.');
            }

            $this->defaultExpirationHours = $hours;
        }
    }

    protected function pruneSetHours(): void
    {
        if ($this->expirationHours) {
            foreach ($this->expirationHours as $type => $hours) {
                $this->info("Pruning records of type '{$type}' older than {$hours} hours...");

                $expirationDate = Carbon::now()->subHours($hours);
                $count = app(TelescopeRepository::class)->pruneByEventType([$type], $expirationDate);

                $this->info("Deleted {$count} records.");
            }
        }
    }

    protected function pruneHours(): void
    {
        $repository = app(TelescopeRepository::class);

        if (!empty($this->defaultExpirationHours)) {
            $defaultExpirationDate = Carbon::now()->subHours($this->defaultExpirationHours);

            $this->info("Pruning records of other types older than {$this->defaultExpirationHours} hours...");

            if ($this->expirationHours) {
                $types = $this->filterTypes();
                $count = $repository->pruneByEventType($types, $defaultExpirationDate);
            } else {
                $count = $repository->prune($defaultExpirationDate);
            }

            $this->info("Deleted {$count} records.");
        } elseif (!$this->expirationHours) {
            $repository->clear();

            $this->info("Deleted all records.");
        }
    }

    protected function filterTypes(): array
    {
        $excludeTypes = array_keys($this->expirationHours);
        $types = array_diff(self::COMMON_TYPES, $excludeTypes);

        if (
            in_array(self::UNRESOLVED_EXCEPTION, $excludeTypes)
            || in_array(self::RESOLVED_EXCEPTION, $excludeTypes)
        ) {
            $types = array_diff($types, [EntryType::EXCEPTION]);

            if (!in_array(self::EXCEPTION_TYPES, $excludeTypes)) {
                $types = [
                    ...$types,
                    (in_array(self::UNRESOLVED_EXCEPTION, $excludeTypes))
                        ? self::RESOLVED_EXCEPTION
                        : self::UNRESOLVED_EXCEPTION
                ];
            }
        }

        return $types;
    }
}
