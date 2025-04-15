<?php

namespace App\Livewire;

use Livewire\Component;
use Carbon\Carbon;

class LiveDateTime extends Component
{
    public $currentDateTime;

    public function mount()
    {
        $this->updateDateTime();
    }

    public function updateDateTime()
    {
        $this->currentDateTime = Carbon::now()->format('Y-m-d h:i:s A');
    }

    public function render()
    {
        return view('livewire.live-date-time');
    }
}
