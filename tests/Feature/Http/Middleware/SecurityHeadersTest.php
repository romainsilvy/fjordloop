<?php

namespace Tests\Feature\Http\Middleware;

use App\Http\Middleware\SecurityHeaders;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Tests\TestCase;

class SecurityHeadersTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     */
    public function adds_security_headers_to_response()
    {
        $middleware = new SecurityHeaders;
        $request = Request::create('/test');

        $response = $middleware->handle($request, function ($request) {
            return new Response('Test response');
        });

        // Test des en-têtes communs
        expect($response->headers->get('X-Content-Type-Options'))->toBe('nosniff');
        expect($response->headers->get('X-Frame-Options'))->toBe('SAMEORIGIN');
        expect($response->headers->get('Referrer-Policy'))->toBe('no-referrer-when-downgrade');
        expect($response->headers->get('Content-Security-Policy'))->toContain("default-src 'self'");
    }

    /**
     * @test
     */
    public function adds_hsts_header_for_https_requests()
    {
        $middleware = new SecurityHeaders;
        $request = Request::create('https://example.com/test');

        $response = $middleware->handle($request, function ($request) {
            return new Response('Test response');
        });

        expect($response->headers->get('Strict-Transport-Security'))
            ->toBe('max-age=31536000; includeSubDomains');
    }

    /**
     * @test
     */
    public function does_not_add_hsts_header_for_http_requests()
    {
        $middleware = new SecurityHeaders;
        $request = Request::create('http://example.com/test');

        $response = $middleware->handle($request, function ($request) {
            return new Response('Test response');
        });

        expect($response->headers->get('Strict-Transport-Security'))->toBeNull();
    }

    /**
     * @test
     */
    public function uses_strict_csp_in_non_local_environment()
    {
        // Dans l'environnement de test (pas 'local'), doit utiliser la CSP stricte
        $middleware = new SecurityHeaders;
        $request = Request::create('/test');

        $response = $middleware->handle($request, function ($request) {
            return new Response('Test response');
        });

        $csp = $response->headers->get('Content-Security-Policy');

        // En non-local, CSP stricte - contient script-src 'self' mais pas les directives Vite
        expect($csp)->toContain("script-src 'self'");
        expect($csp)->toContain("default-src 'self'");
        expect($csp)->toContain("frame-ancestors 'self'");
        // Ne devrait pas contenir les extensions locales
        expect($csp)->not->toContain('localhost');
        expect($csp)->not->toContain(':5173');
    }

    /**
     * @test
     */
    public function vite_csp_in_local_with_env()
    {
        // Sauvegarder l'environnement actuel
        $originalEnv = app()->environment();

        // Configurer temporairement l'environnement
        putenv('APP_ENV=local');
        app()['env'] = 'local';

        // Configurer Vite
        config(['vite.dev_server' => 'http://localhost:5173']);

        $middleware = new SecurityHeaders;
        $request = Request::create('/test');

        $response = $middleware->handle($request, function ($request) {
            return new Response('Test response');
        });

        $csp = $response->headers->get('Content-Security-Policy');

        // Doit inclure les directives Vite locales
        expect($csp)->toContain('unsafe-eval');
        expect($csp)->toContain('http://localhost:5173');
        expect($csp)->toContain('ws://localhost:5173');

        // Restaurer l'environnement
        putenv("APP_ENV={$originalEnv}");
        app()['env'] = $originalEnv;
    }
}
