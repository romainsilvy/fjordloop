<?php

namespace App\Livewire;

use App\Services\NominatimService;
use Livewire\Attributes\Modelable;
use Livewire\Component;

class SearchMap extends Component
{
    public $query = '';

    public $results = [];

    #[Modelable]
    public $place = [];

    public function updatedQuery(NominatimService $nominatimService)
    {
        if (strlen($this->query) < 3) {
            $this->results = [];

            return;
        }

        $response = $nominatimService->searchPlaceWithGeojson($this->query);

        if ($response->successful()) {
            $this->results = $response->json();
        } else {
            $this->results = [];
        }
    }

    public function selectLocation($lat, $lon, $geojson, $name)
    {
        $this->dispatch(
            'location-selected',
            lat: $lat,
            lon: $lon,
            geojson: $geojson,
            name: $name
        );

        $this->query = $name;
        $this->results = [];
        $this->place = [
            'display_name' => $name,
            'lat' => $lat,
            'lng' => $lon,
            'geojson' => $geojson,
        ];
    }

    public function render()
    {
        return view('livewire.search-map');
    }
}
