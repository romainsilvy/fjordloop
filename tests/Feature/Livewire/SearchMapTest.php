<?php

use App\Livewire\SearchMap;
use Livewire\Livewire;

it('renders search map component', function () {
    // Mock to avoid API calls during rendering
    $this->mockMapboxApi();

    Livewire::test(SearchMap::class)
        ->assertStatus(200);
});

it('initializes with empty query and results', function () {
    Livewire::test(SearchMap::class)
        ->assertSet('query', '')
        ->assertSet('results', [])
        ->assertSet('place', []);
});

it('initializes with provided place data', function () {
    $place = [
        'display_name' => 'Paris, France',
        'lat' => 48.8566,
        'lng' => 2.3522,
    ];

    Livewire::test(SearchMap::class, ['place' => $place])
        ->assertSet('query', 'Paris, France')
        ->assertSet('place', $place);
});

it('does not search when query is too short', function () {
    Livewire::test(SearchMap::class)
        ->set('query', 'Pa')
        ->assertSet('results', []);
});

it('searches locations when query is long enough', function () {
    // Use a specific mock response for Paris
    $mockResponse = $this->getMapboxResponseForLocation('Paris', 'Paris, France', 2.3522, 48.8566);
    $this->mockMapboxApiWithResponse($mockResponse);

    Livewire::test(SearchMap::class)
        ->set('query', 'Paris')
        ->assertSet('results', [[
            'display_name' => 'Paris, France',
            'lat' => 48.8566,
            'lon' => 2.3522,
            'place_type' => 'place',
            'id' => 'place.paris',
            'context' => [
                'country' => [
                    'name' => 'France',
                    'country_code' => 'FR',
                ],
            ],
        ]]);
});

it('handles empty search results', function () {
    // Mock empty response
    $this->mockMapboxApiWithResponse($this->getEmptyMapboxResponse());

    Livewire::test(SearchMap::class)
        ->set('query', 'NonexistentPlace')
        ->assertSet('results', []);
});

it('handles API errors gracefully', function () {
    // Mock API error
    $this->mockMapboxApiError(500);

    Livewire::test(SearchMap::class)
        ->set('query', 'TestPlace')
        ->assertSet('results', []);
});

it('handles MapboxService exceptions during search', function () {
    // Mock MapboxService to throw an exception
    $this->mock(\App\Services\MapboxService::class, function ($mock) {
        $mock->shouldReceive('searchPlaceWithGeojson')
            ->andThrow(new Exception('Service unavailable'));
    });

    Livewire::test(SearchMap::class)
        ->set('query', 'TestPlace')
        ->assertSet('results', []);
});

it('selects location correctly', function () {
    Livewire::test(SearchMap::class)
        ->call('selectLocation', 48.8566, 2.3522, 'Paris, France')
        ->assertSet('query', 'Paris, France')
        ->assertSet('results', [])
        ->assertSet('place', [
            'display_name' => 'Paris, France',
            'lat' => 48.8566,
            'lng' => 2.3522,
        ])
        ->assertDispatched('location-selected',
            lat: 48.8566,
            lon: 2.3522,
            name: 'Paris, France'
        );
});

it('updates query from external event', function () {
    Livewire::test(SearchMap::class)
        ->dispatch('open-map', lat: 45.7640, lon: 4.8357, name: 'Lyon, France')
        ->assertSet('query', 'Lyon, France');
});

it('cleans up when requested', function () {
    // Set up proper result structure that matches what the component expects
    $validResults = [[
        'display_name' => 'Test Location',
        'lat' => 48.8566,
        'lon' => 2.3522,
        'place_type' => 'place',
        'id' => 'place.test',
        'context' => [],
    ]];

    Livewire::test(SearchMap::class)
        ->set('query', 'Some place')
        ->set('results', $validResults)
        ->dispatch('clean-map')
        ->assertSet('query', '')
        ->assertSet('results', [])
        ->assertSet('place', [
            'display_name' => null,
            'lat' => null,
            'lng' => null,
        ]);
});
