<?php

namespace App\Livewire\Travel;

use App\Models\Travel;
use Livewire\Component;
use Livewire\Attributes\Validate;

class Create extends Component
{
    #[Validate('required')]
    public $name;
    #[Validate('required')]
    public $placeName;
    #[Validate('required|date')]
    public $startDate;
    #[Validate('required|date')]
    public $endDate;

    public $members = [];

    public function save()
    {

        $this->validate();

        $user = auth()->user();

        $travel = Travel::create([
            'name' => $this->name,
            'place_name' => $this->placeName,
            // 'start_date' => $this->dateRange['start'],
            // 'end_date' => $this->dateRange['end'],
            'start_date' => $this->startDate,
            'end_date' => $this->endDate,
        ]);

        $travel->attachOwner($user);
        $travel->inviteMembers($this->members, $user);
    }

    public function render()
    {
        return view('livewire.travel.create');
    }
}
