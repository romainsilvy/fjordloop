<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SecurityHeaders
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        // HSTS seulement en HTTPS
        if ($request->isSecure()) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }

        // En-têtes communs
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        $response->headers->set('Referrer-Policy', 'no-referrer-when-downgrade');

        // ---- CSP à partir de la config ----
        $directives = [
            'default-src' => config('csp.default_src', ["'self'"]),
            'img-src' => config('csp.img_src', ["'self'", 'data:', 'blob:']),
            'font-src' => config('csp.font_src', ["'self'", 'data:']),
            'style-src' => config('csp.style_src', ["'self'", "'unsafe-inline'"]),
            'script-src' => config('csp.script_src', ["'self'"]),
            'connect-src' => config('csp.connect_src', ["'self'"]),
            'worker-src' => config('csp.worker_src', ["'self'", 'blob:']),
            'frame-ancestors' => config('csp.frame_ancestors', ["'self'"]),
        ];

        // En local : autoriser le dev server Vite (HTTP/HTTPS + WS) et blobs
        if (app()->environment('local')) {
            $vite = rtrim((string) config('vite.dev_server'), '/');
            $parts = parse_url($vite) ?: [];
            $scheme = $parts['scheme'] ?? 'http';
            $host = $parts['host'] ?? 'localhost';
            $port = $parts['port'] ?? 5173;

            $origin = "{$scheme}://{$host}:{$port}";
            $wsScheme = $scheme === 'https' ? 'wss' : 'ws';
            $wsOrigin = "{$wsScheme}://{$host}:{$port}";

            // script/style/connect/worker pour Vite + HMR
            $this->push($directives['style-src'], [$origin, "'unsafe-inline'"]);
            $this->push($directives['script-src'], [$origin, "'unsafe-inline'", "'unsafe-eval'", 'blob:']);
            $this->push($directives['connect-src'], [$origin, $wsOrigin]);
            $this->push($directives['worker-src'], ['blob:']);
            // images/ fonts servies par Vite (rare, mais harmless)
            $this->push($directives['img-src'], [$origin, 'blob:']);
            $this->push($directives['font-src'], [$origin, 'data:']);

            // Optionnel: log en Report-Only pour debug CSP sans bloquer
            $reportOnly = $this->build($directives);
            $response->headers->set('Content-Security-Policy-Report-Only', $reportOnly);
        }

        // En-tête CSP effectif
        $response->headers->set('Content-Security-Policy', $this->build($directives));

        return $response;
    }

    private function push(array &$arr, array $values): void
    {
        foreach ($values as $v) {
            if (! in_array($v, $arr, true)) {
                $arr[] = $v;
            }
        }
    }

    private function build(array $directives): string
    {
        return collect($directives)
            ->map(fn ($vals, $key) => $key . ' ' . implode(' ', array_unique($vals)))
            ->implode('; ');
    }
}
