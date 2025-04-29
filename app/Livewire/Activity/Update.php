<?php

namespace App\Livewire\Activity;

use Flux\Flux;
use Livewire\Component;
use App\Models\Activity;
use Masmerise\Toaster\Toaster;
use Livewire\Attributes\Validate;

use function PHPUnit\Framework\isNull;

class Update extends Component
{
    public Activity $activity;

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

    public function mount(Activity $activity)
    {
        $this->activity = $activity;

        if ($this->activity) {

            if ($this->activity->price_by_person) {
                $this->priceType = 'price_by_person';
            } elseif ($this->activity->price_by_group) {
                $this->priceType = 'price_by_group';
            } else {
                $this->priceType = 'price_by_person';
            }

            $this->name = $this->activity->name;
            $this->description = $this->activity->description;
            $this->place['display_name'] = $this->activity->place_name;
            $this->place['lat'] = $this->activity->place_latitude;
            $this->place['lng'] = $this->activity->place_longitude;
            $this->place['geojson'] = $this->activity->place_geojson;
            $this->url = $this->activity->url;
            $this->price = $this->activity->{$this->priceType};
        }
    }

    public function save()
    {
        $this->validate();

        $user = auth()->user();

        $activity = $this->activity->update([
            'name' => $this->name,
            'description' => $this->description,
            'place_name' => $this->place['display_name'],
            'place_latitude' => $this->place['lat'],
            'place_longitude' => $this->place['lng'],
            'place_geojson' => $this->place['geojson'],
            'url' => $this->url,
            'price_by_person' => $this->priceType == 'price_by_person' ? $this->price : null,
            'price_by_group' => $this->priceType == 'price_by_group' ? $this->price : null,
        ]);


        $this->dispatch('activityUpdated');
        Flux::modals()->close();
        Toaster::success( 'Activité modifiée !');
    }

    public function delete()
    {
        $this->activity->delete();

        $this->dispatch('activityDeleted');
        Flux::modals()->close();
        Toaster::success( 'Activité supprimée !');
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

        $this->dispatch('clean-map');
    }


    public function render()
    {
        return view('livewire.activity.update');
    }
}
