<?php

namespace App\Livewire;

use App\Services\MapboxService;
use Livewire\Component;
use Livewire\Attributes\On;
use App\Services\NominatimService;
use Livewire\Attributes\Modelable;
use Illuminate\Support\Facades\Log;
use Masmerise\Toaster\Toaster;


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

    public function updatedQuery(MapboxService $mapboxService)
    {
        if (strlen($this->query) < 3) {
            $this->results = [];

            return;
        }

        try {
            $response = $mapboxService->searchPlaceWithGeojson($this->query);

            if ($response->successful()) {
                $formattedResults = $mapboxService->formatSearchResults($response);
                $this->results = $formattedResults;

                if (empty($formattedResults)) {
                    Toaster::info('Aucun résultat trouvé pour votre recherche');
                }
            }
        } catch (\Exception $e) {
            // log the error
            Log::error('Nominatim search error: ' . $e->getMessage());
            Toaster::error('Une erreur est survenue dans la recherche, nos équipes ont été prévenues. Merci de réessayer plus tard');

            $this->results = [];

            return;
        }
    }

    #[On('open-map')]
    public function updateQueryFromEvent($lat, $lon, $name)
    {
        $this->query = $name;
    }

    public function selectLocation($lat, $lon, $name)
    {
        $this->dispatch(
            'location-selected',
            lat: $lat,
            lon: $lon,
            name: $name
        );

        $this->query = $name;
        $this->results = [];
        $this->place = [
            'display_name' => $name,
            'lat' => $lat,
            'lng' => $lon,
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
        ];
    }

    public function render()
    {
        return view('livewire.search-map');
    }
}
