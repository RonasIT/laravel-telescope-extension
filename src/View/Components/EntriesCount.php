<?php

namespace RonasIT\TelescopeExtension\View\Components;

use RonasIT\TelescopeExtension\Repositories\TelescopeRepository;
use Illuminate\View\Component;

class EntriesCount extends Component
{
    public string $type;
    public string $label;

    public function __construct(string $type, string $label)
    {
        $this->type = $type;
        $this->label = $label;
    }

    public function render()
    {
        $count = app(TelescopeRepository::class)->countByType($this->type);

        return ($count > 0) ? "{$this->label} ({$count})" : $this->label;
    }
}
