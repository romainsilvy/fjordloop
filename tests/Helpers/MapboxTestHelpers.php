<?php

namespace Tests\Helpers;

/**
 * Helper functions for creating Mapbox test data
 */
class MapboxTestHelpers
{
    /**
     * Get a realistic Mapbox response for testing French locations
     */
    public static function getFrenchLocationResponse(string $name, string $fullAddress, float $lon, float $lat, string $featureType = 'place'): array
    {
        return [
            'type' => 'FeatureCollection',
            'features' => [
                [
                    'type' => 'Feature',
                    'properties' => [
                        'name' => $name,
                        'full_address' => $fullAddress,
                        'feature_type' => $featureType,
                        'context' => [
                            'country' => [
                                'name' => 'France',
                                'country_code' => 'FR',
                                'country_code_alpha_3' => 'FRA',
                            ],
                            'region' => [
                                'name' => 'Île-de-France',
                                'region_code' => 'IDF',
                            ],
                        ],
                    ],
                    'geometry' => [
                        'type' => 'Point',
                        'coordinates' => [$lon, $lat],
                    ],
                    'id' => 'place.' . strtolower(str_replace(' ', '.', $name)),
                ],
            ],
        ];
    }

    /**
     * Get a response with multiple search results
     */
    public static function getMultipleResultsResponse(): array
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
                    'id' => 'place.paris',
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
                    'id' => 'place.lyon',
                ],
                [
                    'type' => 'Feature',
                    'properties' => [
                        'name' => 'Marseille',
                        'full_address' => 'Marseille, France',
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
                        'coordinates' => [5.3698, 43.2965],
                    ],
                    'id' => 'place.marseille',
                ],
            ],
        ];
    }

    /**
     * Get response for address searches
     */
    public static function getAddressResponse(string $address, string $city, float $lon, float $lat): array
    {
        return [
            'type' => 'FeatureCollection',
            'features' => [
                [
                    'type' => 'Feature',
                    'properties' => [
                        'name' => $address,
                        'full_address' => "{$address}, {$city}, France",
                        'feature_type' => 'address',
                        'context' => [
                            'place' => [
                                'name' => $city,
                            ],
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
                    'id' => 'address.' . strtolower(str_replace(' ', '.', $address)),
                ],
            ],
        ];
    }
}
