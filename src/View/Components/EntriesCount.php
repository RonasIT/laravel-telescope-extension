<?php

namespace RonasIT\TelescopeExtension\View\Components;

use Illuminate\Support\Facades\DB;
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
        $count = DB::table('telescope_entries')->where('type', $this->type)->count();

        return ($count > 0) ? "{$this->label} ({$count})" : $this->label;
    }
}
