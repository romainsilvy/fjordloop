<?php

namespace App\Livewire\Travel;

use App\Models\Activity;
use App\Models\Travel;
use Livewire\Component;
use Livewire\Attributes\On;

class Show extends Component
{
    public $travel;

    public $activities;

    public function mount($travelId)
    {
        $this->travel = Travel::findOrFail($travelId);

        $this->refreshActivities();
    }

    public function selectActivity($activityId)
    {
        $this->dispatch('open-activity-modal', $activityId);
    }

    #[On('activityCreated')]
    #[On('activityUpdated')]
    #[On('activityDeleted')]
    public function refreshActivities()
    {
        $this->activities = $this->travel->activities()->get();

        $this->dispatch('activities-refreshed', $this->activities);
    }

    public function render()
    {
        return view('livewire.travel.show');
    }
}
