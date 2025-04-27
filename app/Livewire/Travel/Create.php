<?php

namespace App\Livewire\Travel;

use App\Models\Travel;
use Livewire\Component;
use Livewire\Attributes\Validate;

class Create extends Component
{
    #[Validate('required')]
    public $name;

    public $place =[
        'display_name' => null,
        'lat' => null,
        'lng' => null,
        'geojson' => null,
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
            'place_geojson' => $this->place['geojson'],
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
