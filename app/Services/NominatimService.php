<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class NominatimService
{
    protected $baseUrl;

    public function __construct()
    {
        $this->baseUrl = config('services.nominatim.url').'/search';
    }

    public function searchPlaceWithGeojson(string $q)
    {
        return Http::get($this->baseUrl, [
            'format' => 'json',
            'polygon_geojson' => 1,
            'q' => $q,
        ]);
    }
}
