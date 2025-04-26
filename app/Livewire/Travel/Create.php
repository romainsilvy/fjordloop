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

    public $members = [];

    public array $dateRange = [
        'start' => null,
        'end' => null,
    ];

    public function save()
    {

        $this->validate();

        $user = auth()->user();

        $travel = Travel::create([
            'name' => $this->name,
            'place_name' => $this->placeName,
            'start_date' => $this->dateRange['start'],
            'end_date' => $this->dateRange['end'],
        ]);

        $travel->attachOwner($user);
        $travel->inviteMembers($this->members, $user);

        return redirect()->route('travel.show', [
            'travelId' => $travel->id,
        ]);
    }

    public function render()
    {
        return view('livewire.travel.create');
    }
}
