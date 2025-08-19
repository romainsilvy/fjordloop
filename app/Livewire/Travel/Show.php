<?php

namespace App\Livewire\Travel;

use App\Models\Travel;
use Livewire\Attributes\On;
use Livewire\Component;

class Show extends Component
{
    public $travel;

    public $activities;
    public $housings;

    public function mount($travelId)
    {
        $this->travel = Travel::findOrFail($travelId);

        $this->authorize('view', $this->travel);

        $this->refreshActivities();
        $this->refreshHousings();
    }

    #[On('activityCreated')]
    public function refreshActivities()
    {
        $this->activities = $this->travel->activities()->get();

        $this->dispatch('activities-refreshed', $this->activities);
    }

    #[On('housingCreated')]
    public function refreshHousings()
    {
        $this->housings = $this->travel->housings()->get();

        $this->dispatch('housings-refreshed', $this->housings);
    }

    public function render()
    {
        return view('livewire.travel.show');
    }
}
