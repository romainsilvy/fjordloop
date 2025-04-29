<?php

namespace App\Livewire\Activity;

use Flux\Flux;
use App\Models\Travel;
use Livewire\Component;
use Masmerise\Toaster\Toaster;
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

    #[Validate('url|nullable')]
    public $url;

    public $availablePrices = [
        'price_by_person' => 'par personne',
        'price_by_group' => 'pour le groupe',
    ];

    #[Validate('numeric|nullable')]
    public $price;
    public $priceType = 'price_by_person';

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
            'place_name' => $this->place['display_name'],
            'place_latitude' => $this->place['lat'],
            'place_longitude' => $this->place['lng'],
            'place_geojson' => $this->place['geojson'],
            'url' => $this->url,
            'price_by_person' => $this->priceType == 'price_by_person' ? $this->price : null,
            'price_by_group' => $this->priceType == 'price_by_group' ? $this->price : null,
            // 'date_range' => json_encode($this->dateRange),
        ]);

        $this->cleanupFields();

        $this->dispatch('activityCreated');
        Flux::modals()->close();
        Toaster::success( 'Activité créée!');
    }

    public function cleanupFields()
    {
        $this->name = null;
        $this->description = null;
        $this->place = [
            'display_name' => null,
            'lat' => null,
            'lng' => null,
            'geojson' => null,
        ];
        $this->url = null;
        $this->priceType = 'price_by_person';
        $this->price = null;
    }

    public function render()
    {
        return view('livewire.activity.create');
    }
}
