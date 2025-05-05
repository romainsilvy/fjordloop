<?php

namespace App\Livewire\Housing;

use App\Models\Travel;
use Livewire\Component;

class Show extends Component
{
    public $travel;
    public $housing;

    public function mount($travelId, $housingId)
    {
        $this->travel = Travel::findOrFail($travelId);

        $this->housing = $this->travel->housings()->findOrFail($housingId);
    }
    public function render()
    {
        return view('livewire.housing.show');
    }
}
