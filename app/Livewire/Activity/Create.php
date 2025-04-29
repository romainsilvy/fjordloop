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
    #[Validate('numeric|nullable')]
    public $priceByPerson;
    #[Validate('numeric|nullable')]
    public $priceByGroup;

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
            // 'place' => $this->place,
            'url' => $this->url,
            'price_by_person' => $this->priceByPerson,
            'price_by_group' => $this->priceByGroup,
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
        $this->priceByPerson = null;
        $this->priceByGroup = null;
    }

    public function render()
    {
        return view('livewire.activity.create');
    }
}
