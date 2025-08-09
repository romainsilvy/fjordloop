# Mapbox API Mocking in Tests

This guide explains how Mapbox API calls are mocked in our test suite to ensure reliable, fast, and offline testing.

## Overview

All tests automatically mock the Mapbox API to avoid:
- Real API calls during testing
- Network dependencies
- Rate limiting issues
- Inconsistent responses
- API key requirements

## Automatic Mocking

The `TestCase` class automatically sets up Mapbox API mocking for all tests:

```php
// In TestCase.php
protected function setUp(): void
{
    parent::setUp();
    
    // Mock Mapbox API by default for all tests
    $this->mockMapboxApi();
    
    // Set a fake Mapbox API key for testing
    config(['services.mapbox.key' => 'fake-mapbox-key-for-testing']);
}
```

## Available Mocking Methods

### Basic Mocking Methods

#### `mockMapboxApi()`
Sets up default mocking with a standard response containing Paris and Lyon.

```php
$this->mockMapboxApi();
```

#### `mockMapboxApiWithResponse($responseData, $statusCode = 200)`
Mock with a specific response.

```php
$responseData = $this->getDefaultMapboxResponse();
$this->mockMapboxApiWithResponse($responseData);
```

#### `mockMapboxApiError($statusCode = 500)`
Mock API to return an error response.

```php
$this->mockMapboxApiError(500);
```

### Response Helper Methods

#### `getDefaultMapboxResponse()`
Returns a standard response with Paris and Lyon features.

#### `getMapboxResponseForLocation($name, $fullAddress, $lon, $lat)`
Creates a response for a specific location.

```php
$response = $this->getMapboxResponseForLocation('Nice', 'Nice, France', 7.2619, 43.7102);
$this->mockMapboxApiWithResponse($response);
```

#### `getEmptyMapboxResponse()`
Returns an empty response (no features found).

```php
$emptyResponse = $this->getEmptyMapboxResponse();
$this->mockMapboxApiWithResponse($emptyResponse);
```

## Example Test Cases

### Testing MapboxService Directly

```php
it('searches for a location', function () {
    $mockResponse = $this->getMapboxResponseForLocation('Nice', 'Nice, France', 7.2619, 43.7102);
    $this->mockMapboxApiWithResponse($mockResponse);

    $service = new MapboxService();
    $response = $service->searchPlaceWithGeojson('Nice');
    
    expect($response->successful())->toBeTrue();
    expect($response->json())->toBe($mockResponse);
});
```

### Testing Livewire Components

```php
it('searches locations in SearchMap component', function () {
    $mockResponse = $this->getMapboxResponseForLocation('Lyon', 'Lyon, France', 4.8357, 45.7640);
    $this->mockMapboxApiWithResponse($mockResponse);

    Livewire::test(SearchMap::class)
        ->set('query', 'Lyon')
        ->assertSet('results', [[
            'display_name' => 'Lyon, France',
            'lat' => 45.7640,
            'lon' => 4.8357,
            'place_type' => 'place',
            'id' => 'place.lyon',
            'context' => [
                'country' => [
                    'name' => 'France',
                    'country_code' => 'FR',
                ],
            ],
        ]]);
});
```

### Testing Error Scenarios

```php
it('handles API errors gracefully', function () {
    $this->mockMapboxApiError(500);

    Livewire::test(SearchMap::class)
        ->set('query', 'TestPlace')
        ->assertSet('results', []);
});
```

### Testing Empty Results

```php
it('handles empty search results', function () {
    $this->mockMapboxApiWithResponse($this->getEmptyMapboxResponse());

    Livewire::test(SearchMap::class)
        ->set('query', 'NonexistentPlace')
        ->assertSet('results', []);
});
```

## Helper Classes

### MapboxTestHelpers

For more complex scenarios, use the `MapboxTestHelpers` class:

```php
use Tests\Helpers\MapboxTestHelpers;

// French location with realistic context
$response = MapboxTestHelpers::getFrenchLocationResponse('Toulouse', 'Toulouse, France', 1.4442, 43.6047);
$this->mockMapboxApiWithResponse($response);

// Multiple results
$multipleResults = MapboxTestHelpers::getMultipleResultsResponse();
$this->mockMapboxApiWithResponse($multipleResults);

// Address search
$addressResponse = MapboxTestHelpers::getAddressResponse('10 Rue de Rivoli', 'Paris', 2.3522, 48.8566);
$this->mockMapboxApiWithResponse($addressResponse);
```

## Best Practices

1. **Use specific mocks**: Create specific responses for your test scenarios rather than relying only on defaults.

2. **Test edge cases**: Mock empty responses, error responses, and malformed data.

3. **Test caching**: The MapboxService includes caching, so test both cached and uncached scenarios.

4. **Mock at the right level**: Mock at the HTTP level (using `Http::fake()`) rather than mocking the service classes directly.

5. **Realistic data**: Use realistic coordinates and addresses in your mock responses.

## Cache Testing

The MapboxService caches responses. Test both scenarios:

```php
it('uses cached data when available', function () {
    $query = 'Paris';
    $cacheKey = 'mapbox_search_' . md5($query);
    $cachedData = ['features' => ['cached data']];

    Cache::shouldReceive('get')
        ->with($cacheKey)
        ->once()
        ->andReturn($cachedData);

    $service = new MapboxService();
    $response = $service->searchPlaceWithGeojson($query);

    expect($response->json())->toBe($cachedData);
});
```

## Configuration

Ensure your test environment doesn't require a real Mapbox API key:

- Tests automatically set `services.mapbox.key` to a fake value
- Real API calls are intercepted by `Http::fake()`
- No environment variables are needed for testing

## Troubleshooting

If you see real API calls in tests:

1. Ensure your test extends the base `TestCase` class
2. Check that `Http::fake()` is called before the service method
3. Verify the URL pattern in `Http::fake()` matches the actual API endpoint
4. Make sure you're not clearing HTTP fakes inadvertently

For integration tests that might make real calls, explicitly mock at the start of your test:

```php
it('integration test with mocked mapbox', function () {
    $this->mockMapboxApi();
    
    // Your integration test code here
});
```
