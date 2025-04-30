<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\On;
use App\Services\NominatimService;
use Livewire\Attributes\Modelable;

class SearchMap extends Component
{
    public $query = '';

    public $results = [];

    #[Modelable]
    public $place = [];

    public function mount()
    {
        $this->query = $this->place['display_name'] ?? '';
    }

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

    #[On('open-map')]
    public function updateQueryFromEvent($lat, $lon, $geojson, $name)
    {
        $this->query = $name;
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

    #[On('clean-map')]
    public function cleanUp()
    {
        $this->query = '';
        $this->results = [];
        $this->place = [
            'display_name' => null,
            'lat' => null,
            'lng' => null,
            'geojson' => null,
        ];
    }

    public function render()
    {
        return view('livewire.search-map');
    }
}
