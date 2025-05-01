<?php

namespace App\Livewire\Activity;

use App\Models\Travel;
use Livewire\Component;
use App\Models\Activity;

class Show extends Component
{
    public $travel;
    public $activity;

    public function mount($travelId, $activityId)
    {
        $this->travel = Travel::findOrFail($travelId);

        $this->activity = $this->travel->activities()->findOrFail($activityId);
    }

    public function render()
    {
        return view('livewire.activity.show');
    }
}
