<?php

namespace Tests;

use Illuminate\Support\Facades\Http;

trait MocksMapboxApi
{
    /**
     * Set up Mapbox API mocking with default responses
     */
    protected function mockMapboxApi(): void
    {
        Http::fake([
            'api.mapbox.com/*' => Http::response($this->getDefaultMapboxResponse(), 200),
        ]);
    }

    /**
     * Mock Mapbox API with a specific response
     */
    protected function mockMapboxApiWithResponse(array $responseData, int $statusCode = 200): void
    {
        Http::fake([
            'api.mapbox.com/*' => Http::response($responseData, $statusCode),
        ]);
    }

    /**
     * Mock Mapbox API to return an error
     */
    protected function mockMapboxApiError(int $statusCode = 500): void
    {
        Http::fake([
            'api.mapbox.com/*' => Http::response([], $statusCode),
        ]);
    }

    /**
     * Get a default successful Mapbox response for testing
     */
    protected function getDefaultMapboxResponse(): array
    {
        return [
            'type' => 'FeatureCollection',
            'features' => [
                [
                    'type' => 'Feature',
                    'properties' => [
                        'name' => 'Paris',
                        'full_address' => 'Paris, France',
                        'feature_type' => 'place',
                        'context' => [
                            'country' => [
                                'name' => 'France',
                                'country_code' => 'FR',
                            ],
                        ],
                    ],
                    'geometry' => [
                        'type' => 'Point',
                        'coordinates' => [2.3522, 48.8566],
                    ],
                    'id' => 'place.paris.france',
                ],
                [
                    'type' => 'Feature',
                    'properties' => [
                        'name' => 'Lyon',
                        'full_address' => 'Lyon, France',
                        'feature_type' => 'place',
                        'context' => [
                            'country' => [
                                'name' => 'France',
                                'country_code' => 'FR',
                            ],
                        ],
                    ],
                    'geometry' => [
                        'type' => 'Point',
                        'coordinates' => [4.8357, 45.7640],
                    ],
                    'id' => 'place.lyon.france',
                ],
            ],
        ];
    }

    /**
     * Get a Mapbox response for a specific location
     */
    protected function getMapboxResponseForLocation(string $name, string $fullAddress, float $lon, float $lat): array
    {
        return [
            'type' => 'FeatureCollection',
            'features' => [
                [
                    'type' => 'Feature',
                    'properties' => [
                        'name' => $name,
                        'full_address' => $fullAddress,
                        'feature_type' => 'place',
                        'context' => [
                            'country' => [
                                'name' => 'France',
                                'country_code' => 'FR',
                            ],
                        ],
                    ],
                    'geometry' => [
                        'type' => 'Point',
                        'coordinates' => [$lon, $lat],
                    ],
                    'id' => 'place.' . strtolower($name),
                ],
            ],
        ];
    }

    /**
     * Get an empty Mapbox response (no results found)
     */
    protected function getEmptyMapboxResponse(): array
    {
        return [
            'type' => 'FeatureCollection',
            'features' => [],
        ];
    }
}
