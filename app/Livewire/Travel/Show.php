<?php

namespace App\Livewire\Travel;

use App\Models\Activity;
use App\Models\Travel;
use Livewire\Component;
use Livewire\Attributes\On;

class Show extends Component
{
    public $travel;

    public $token;

    public $isInvited = false;

    public $activities;

    public function mount($travelId, $token = null)
    {
        $this->token = $token;

        if ($this->token) {
            $this->travel = Travel::fromInvitation($this->token);

            if ($this->travel) {
                $this->isInvited = true;
            } else {
                abort(404);
            }
        } else {
            $this->travel = Travel::findOrFail($travelId);
        }

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
