<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class MapboxService
{
    protected $apiKey;
    protected $baseUrl = 'https://api.mapbox.com/search/geocode/v6/forward';

    public function __construct()
    {
        $this->apiKey = config('services.mapbox.key');
    }

    /**
     * Recherche un lieu avec Mapbox et inclut les données GeoJSON
     *
     * @param string $query Le terme de recherche
     * @return \Illuminate\Http\Client\Response
     */
    public function searchPlaceWithGeojson($query)
    {
        if (empty($this->apiKey)) {
            throw new \Exception("Mapbox API key is not configured");
        }

        $cacheKey = 'mapbox_search_' . md5($query);

        // Ne pas mettre en cache l'objet de réponse, mais les données brutes
        $cachedData = Cache::get($cacheKey);

        if ($cachedData) {
            return new \Illuminate\Http\Client\Response(
                new \GuzzleHttp\Psr7\Response(200, [], json_encode($cachedData))
            );
        }

        $response = Http::get($this->baseUrl, [
            'q' => $query,
            'access_token' => $this->apiKey,
            'types' => 'place,locality,neighborhood,address',
            'limit' => 5,
            'language' => 'fr',
        ]);


        if ($response->successful()) {
            Cache::put($cacheKey, $response->json(), 3600);
        }

        return $response;
    }

    public function formatSearchResults($response)
    {
        if (!$response->successful()) {
            return [];
        }

        $data = $response->json();


        if (!isset($data['features']) || !is_array($data['features'])) {
            Log::warning('Mapbox response missing "features" key', ['response' => $data]);
            return [];
        }

        $results = [];

        foreach ($data['features'] as $feature) {
            $results[] = [
                'display_name' => $feature['properties']['full_address'] ?? $feature['properties']['name'] ?? '',
                'lat' => $feature['geometry']['coordinates'][1] ?? null,
                'lon' => $feature['geometry']['coordinates'][0] ?? null,
                'place_type' => $feature['properties']['feature_type'] ?? null,
                'id' => $feature['id'] ?? null,
                'context' => $feature['properties']['context'] ?? [],
            ];
        }

        return $results;
    }
}
