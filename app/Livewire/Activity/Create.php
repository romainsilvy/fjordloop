<?php

namespace App\Livewire\Activity;

use App\Models\Travel;
use Livewire\Component;
use Livewire\Attributes\Validate;

class Create extends Component
{
    public Travel $travel;

    #[Validate('required')]
    public $name;
    #[Validate('required')]
    public $description;

    public $place = [
        'display_name' => null,
        'lat' => null,
        'lng' => null,
        'geojson' => null,
    ];

    // #[Validate('url')]
    // public $url;
    // #[Validate('numeric')]
    // public $priceByPerson;
    // #[Validate('numeric')]
    // public $priceByGroup;

    // public array $dateRange = [
    //     'start' => null,
    //     'end' => null,
    // ];

    public function mount(Travel $travel)
    {
        $this->travel = $travel;
    }

    public function save()
    {
        $this->validate();

        $user = auth()->user();

        $activity = $this->travel->activities()->create([
            'name' => $this->name,
            'description' => $this->description,
            'place' => $this->place,
            'url' => $this->url,
            'price_by_person' => $this->priceByPerson,
            'price_by_group' => $this->priceByGroup,
            // 'date_range' => json_encode($this->dateRange),
        ]);

        dd($activity);


    }

    public function render()
    {
        return view('livewire.activity.create');
    }
}
