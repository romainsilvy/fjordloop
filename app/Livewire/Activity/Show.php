<?php

namespace App\Livewire\Activity;

use App\Models\Travel;
use Livewire\Attributes\On;
use Livewire\Component;

class Show extends Component
{
    public $travel;
    public $activity;
    public $medias;

    public function mount($travelId, $activityId)
    {
        $this->travel = Travel::findOrFail($travelId);

        $this->activity = $this->travel->activities()->findOrFail($activityId);
        $this->medias = $this->activity->getMediaDisplay();
    }

    #[On('activity-updated')]
    public function refreshActivity()
    {
        $this->activity = $this->travel->activities()->findOrFail($this->activity->id);
        $this->medias = $this->activity->getMediaDisplay();
        $this->dispatch('activity-refreshed', $this->activity);
        $this->dispatch('media-refreshed', $this->medias);
    }

    public function render()
    {
        return view('livewire.activity.show');
    }
}
