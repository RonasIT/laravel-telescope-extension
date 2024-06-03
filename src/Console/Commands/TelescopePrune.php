<?php

namespace RonasIT\TelescopeExtension\Console\Commands;

use RonasIT\TelescopeExtension\Repositories\TelescopeRepository;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Laravel\Telescope\EntryType;
use Exception;
use Throwable;

class TelescopePrune extends Command
{
    //php artisan telescope:prune
    //--set-hours=requests:240,queries:24,unresolved-exceptions:480
    //--hours=100

    const UNRESOLVED_EXCEPTION = 'unresolved_exception';
    const RESOLVED_EXCEPTION = 'resolved_exception';

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
        EntryType::SCHEDULED_TASK,
    ];

    const TYPES = [
        ...self::COMMON_TYPES,
        self::UNRESOLVED_EXCEPTION,
        self::RESOLVED_EXCEPTION,
    ];

    protected $signature = 'telescope:prune
                            {--set-hours= : description}
                            {--hours= : description}';

    protected $description = 'Command description';

    protected array $expirationDates = [];

    protected Carbon $defaultExpirationDate;

    protected array $setHoursTypes = [];

    public function handle(): void
    {
        try {
            $this->validateSetHoursOption();
            $this->validateHoursOption();

            $this->prune();
        } catch (Throwable $exception) {
            $this->error($exception->getMessage());
        }
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

                [$type, $hour] = $parts;

                if (!in_array($type, self::TYPES)) {
                    throw new Exception("Incorrect type value '{$type}'.");
                }

                if (!$hour) {
                    throw new Exception("Hours value for '{$type}' type must be set.");
                }

                if (!is_numeric($hour)) {
                    throw new Exception("Hours value for '{$type}' type must be a number.");
                }

                $this->setHoursTypes[] = $type;
                $this->expirationDates[$type] = Carbon::now()->subHours($hour);
            }
        }
    }

    protected function validateHoursOption(): void
    {
        $value = $this->option('hours');

        if ($value) {
            if (!is_numeric($value)) {
                throw new Exception('Hours value must be a number.');
            }

            $this->defaultExpirationDate = Carbon::now()->subHours($value);
        }
    }

    protected function prune(): void
    {
        $repository = app(TelescopeRepository::class);

        if ($this->expirationDates) {
            foreach ($this->expirationDates as $type => $expirationDate) {
                $this->info("Pruning records of type '{$type}' older than {$expirationDate} hours...");

                $count = $repository->pruneByEventType([$type], $expirationDate);

                $this->info("Deleted {$count} records.");
            }
        }

        if (!empty($this->defaultExpirationDate)) {
            $this->info("Pruning records of other types older than {$this->defaultExpirationDate} hours...");

            if ($this->expirationDates) {
                $types = $this->filterTypes();

                $count = $repository->pruneByEventType($types, $this->defaultExpirationDate);
            } else {
                $count = $repository->prune($this->defaultExpirationDate);
            }

            $this->info("Deleted {$count} records.");
        }
    }

    protected function filterTypes(): array
    {
        $excludeTypes = array_keys($this->expirationDates);
        $types = array_diff(self::COMMON_TYPES, $excludeTypes);

        if (
            in_array(self::UNRESOLVED_EXCEPTION, $this->setHoursTypes)
            || in_array(self::RESOLVED_EXCEPTION, $this->setHoursTypes)
        ) {
            $types = array_diff($types, EntryType::EXCEPTION);

            if (!(in_array(self::UNRESOLVED_EXCEPTION, $this->setHoursTypes) && in_array(self::RESOLVED_EXCEPTION, $this->setHoursTypes))) {
                $types = [
                    ...$types,
                    (in_array(self::UNRESOLVED_EXCEPTION, $this->setHoursTypes))
                        ? self::RESOLVED_EXCEPTION
                        : self::UNRESOLVED_EXCEPTION
                ];
            }
        }

        return $types;
    }
}