<?php

use App\Services\MapboxService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Client\Response;


it('returns cached data if present', function () {
    $query = 'Paris';
    $cacheKey = 'mapbox_search_' . md5($query);
    $cachedData = ['features' => ['some data']];

    Cache::shouldReceive('get')
        ->with($cacheKey)
        ->once()
        ->andReturn($cachedData);

    $service = new MapboxService();

    $response = $service->searchPlaceWithGeojson($query);

    expect($response->json())->toBe($cachedData);
});

it('calls Mapbox and caches result if not in cache', function () {
    $query = 'Lyon';
    $cacheKey = 'mapbox_search_' . md5($query);
    $responseData = ['features' => ['real data']];

    Cache::shouldReceive('get')->with($cacheKey)->once()->andReturn(null);
    Cache::shouldReceive('put')->with($cacheKey, $responseData, 3600)->once();

    Http::fake([
        '*' => Http::response($responseData, 200),
    ]);

    $service = new MapboxService();

    $response = $service->searchPlaceWithGeojson($query);

    expect($response->successful())->toBeTrue();
    expect($response->json())->toBe($responseData);
});

it('throws exception if no API key is configured', function () {
    config()->set('services.mapbox.key', null);

    $service = new MapboxService();

    $service->searchPlaceWithGeojson('Marseille');
})->throws(Exception::class, 'Mapbox API key is not configured');

it('formats Mapbox response correctly', function () {
    $data = [
        'features' => [
            [
                'properties' => [
                    'full_address' => '10 Rue de Rivoli, Paris',
                    'feature_type' => 'address',
                    'context' => ['city' => 'Paris'],
                ],
                'geometry' => [
                    'coordinates' => [2.3522, 48.8566],
                ],
                'id' => 'place.1234',
            ],
        ],
    ];

    $response = new Response(new \GuzzleHttp\Psr7\Response(200, [], json_encode($data)));

    $service = new MapboxService();
    $results = $service->formatSearchResults($response);

    expect($results)->toHaveCount(1)
        ->and($results[0]['display_name'])->toBe('10 Rue de Rivoli, Paris')
        ->and($results[0]['lat'])->toBe(48.8566)
        ->and($results[0]['lon'])->toBe(2.3522);
});

it('returns empty array if response is not successful', function () {
    $response = new Response(new \GuzzleHttp\Psr7\Response(500, [], ''));

    $service = new MapboxService();
    $results = $service->formatSearchResults($response);

    expect($results)->toBe([]);
});

it('logs warning and returns empty array if no features key', function () {
    Log::shouldReceive('warning')
        ->once()
        ->with('Mapbox response missing "features" key', \Mockery::type('array'));

    $response = new Response(new \GuzzleHttp\Psr7\Response(200, [], json_encode(['invalid' => 'data'])));

    $service = new MapboxService();
    $results = $service->formatSearchResults($response);

    expect($results)->toBe([]);
});

