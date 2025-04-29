<?php

namespace App\Livewire\Travel;

use App\Models\Travel;
use Livewire\Component;

class GlobalMap extends Component
{
    public Travel $travel;
    public $activities;

    public function mount(Travel $travel)
    {
        $this->travel = $travel;
        $this->activities = $travel->activities()->hasPlace()->get();
    }


    public function render()
    {
        return view('livewire.travel.global-map');
    }
}
