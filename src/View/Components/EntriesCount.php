<?php

namespace RonasIT\TelescopeExtension\View\Components;

use Illuminate\Support\Str;
use Illuminate\View\Component;
use RonasIT\TelescopeExtension\Repositories\TelescopeRepository;

class EntriesCount extends Component
{
    public string $type;
    public ?string $label;

    public function __construct(string $type, ?string $label = null)
    {
        $this->type = $type;
        $this->label = $label ?? Str::of($type)->plural()->headline();
    }

    public function render()
    {
        $count = app(TelescopeRepository::class)->countByType($this->type);

        return ($count > 0) ? "{$this->label} ({$count})" : $this->label;
    }
}
