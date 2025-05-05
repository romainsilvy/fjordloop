<?php

namespace App\Livewire\Housing;

use App\Models\Travel;
use Livewire\Component;
use Livewire\Attributes\On;

class Show extends Component
{
    public $travel;
    public $housing;

    public function mount($travelId, $housingId)
    {
        $this->travel = Travel::findOrFail($travelId);

        $this->housing = $this->travel->housings()->findOrFail($housingId);
    }

    #[On('housing-updated')]
    public function refreshHousing()
    {
        $this->housing = $this->travel->housings()->findOrFail($this->housing->id);

        $this->dispatch('housing-refreshed', $this->housing);
        $this->dispatch('media-refreshed', $this->housing->getMediaDisplay());

    }

    public function render()
    {
        return view('livewire.housing.show');
    }
}
