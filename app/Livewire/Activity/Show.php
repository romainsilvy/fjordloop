<?php

namespace App\Livewire\Activity;

use App\Models\Travel;
use Livewire\Component;
use App\Models\Activity;
use Livewire\Attributes\On;

class Show extends Component
{
    public $travel;
    public $activity;

    public function mount($travelId, $activityId)
    {
        $this->travel = Travel::findOrFail($travelId);

        $this->activity = $this->travel->activities()->findOrFail($activityId);
    }

    #[On('activity-updated')]
    public function refreshHousing()
    {
        $this->activity = $this->travel->activities()->findOrFail($this->activity->id);
        $this->dispatch('activity-refreshed', $this->activity);
    }

    public function render()
    {
        return view('livewire.activity.show');
    }
}
