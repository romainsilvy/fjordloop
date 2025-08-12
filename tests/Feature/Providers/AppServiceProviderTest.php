<?php

use App\Providers\AppServiceProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\URL;

uses(RefreshDatabase::class);

describe('AppServiceProvider', function () {

    it('forces HTTPS scheme in production environment', function () {
        // Mock the application environment
        $this->app->instance('env', 'production');

        // Spy on URL facade to check if forceScheme is called
        URL::spy();

        // Create and boot the service provider
        $provider = new AppServiceProvider($this->app);
        $provider->boot();

        // Assert that forceScheme was called with 'https'
        URL::shouldHaveReceived('forceScheme')->with('https')->once();
    });

    it('does not force HTTPS scheme in non-production environments', function () {
        // Mock the application environment as local/testing
        $this->app->instance('env', 'local');

        // Spy on URL facade
        URL::spy();

        // Create and boot the service provider
        $provider = new AppServiceProvider($this->app);
        $provider->boot();

        // Assert that forceScheme was not called
        URL::shouldNotHaveReceived('forceScheme');
    });

    it('registers euro blade directive', function () {
        // Spy on Blade facade to check if directive is registered
        Blade::spy();

        // Create and boot the service provider
        $provider = new AppServiceProvider($this->app);
        $provider->boot();

        // Assert that directive was registered with the name 'euro'
        Blade::shouldHaveReceived('directive')->with('euro', Mockery::type('Closure'))->once();
    });

    it('euro blade directive formats numbers correctly', function () {
        // Create and boot the service provider to register the directive
        $provider = new AppServiceProvider($this->app);
        $provider->boot();

        // Test the euro directive with different values
        $testCases = [
            ['input' => '10', 'expected' => '10,00 €'],
            ['input' => '10.5', 'expected' => '10,50 €'],
            ['input' => '1234.56', 'expected' => '1 234,56 €'],
            ['input' => '0', 'expected' => '0,00 €'],
        ];

        foreach ($testCases as $case) {
            // Create a blade template with the euro directive
            $template = "@euro({$case['input']})";

            // Compile the template
            $compiled = Blade::compileString($template);

            // Execute the compiled code and capture output
            ob_start();
            eval('?>' . $compiled);
            $result = ob_get_clean();

            expect($result)->toBe($case['expected']);
        }
    });

    it('register method exists and can be called', function () {
        // Test that the register method exists and doesn't throw errors
        $provider = new AppServiceProvider($this->app);

        // This should not throw any exceptions - just call it directly
        $provider->register();

        // If we get here without exceptions, the test passes
        expect(true)->toBeTrue();
    });

    it('service provider can be instantiated', function () {
        $provider = new AppServiceProvider($this->app);

        expect($provider)->toBeInstanceOf(AppServiceProvider::class);
    });

});
