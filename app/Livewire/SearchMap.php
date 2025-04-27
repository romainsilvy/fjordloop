<?php

namespace App\Livewire;

use App\Services\NominatimService;
use Livewire\Component;
use Illuminate\Support\Facades\Http;

class SearchMap extends Component
{
    public $query = '';
    public $results = [];

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
    }

    public function render()
    {
        return view('livewire.search-map');
    }
}
