<?php

namespace App\Livewire\Travel;

use App\Models\Travel;
use Livewire\Component;

class Show extends Component
{
    public $travel;

    public $token;

    public $isInvited = false;

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
            $this->travel = Travel::find($travelId);
        }
    }

    public function render()
    {
        return view('livewire.travel.show');
    }
}
