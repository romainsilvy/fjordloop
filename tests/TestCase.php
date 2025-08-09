<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use MocksMapboxApi;

    protected function setUp(): void
    {
        parent::setUp();

        // Set a fake Mapbox API key for testing
        config(['services.mapbox.key' => 'fake-mapbox-key-for-testing']);

        // Don't automatically mock - let tests set up their own mocks
        // This prevents interference between default mocks and specific test mocks
    }
}
