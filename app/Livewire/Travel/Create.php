<?php

namespace App\Livewire\Travel;

use App\Models\Travel;
use Livewire\Component;
use Masmerise\Toaster\Toaster;
use Livewire\Attributes\Validate;

class Create extends Component
{
    #[Validate('required')]
    public $name;

    public $place = [
        'display_name' => null,
        'lat' => null,
        'lng' => null,
    ];

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
            'place_name' => $this->place['display_name'],
            'place_latitude' => $this->place['lat'],
            'place_longitude' => $this->place['lng'],
            'start_date' => $this->dateRange['start'],
            'end_date' => $this->dateRange['end'],
        ]);

        $travel->attachOwner($user);
        $travel->inviteMembers($this->members, $user);

        Toaster::success( 'Voyage créée!');
        return redirect()->route('travel.show', [
            'travelId' => $travel->id,
        ]);
    }

    public function cleanupFields()
    {
        $this->name = null;

        $this->place = [
            'display_name' => null,
            'lat' => null,
            'lng' => null,
        ];

        $this->members = [];

        $this->dateRange = [
            'start' => null,
            'end' => null,
        ];

        $this->dispatch('clean-map');
        $this->dispatch('clean-members');
        $this->dispatch('clean-date-range');
    }

    public function render()
    {
        return view('livewire.travel.create');
    }
}
