<?php

namespace App\Providers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }

        Blade::directive('euro', function ($expression) {
            return "<?php echo number_format({$expression}, 2, ',', ' ') . ' €'; ?>";
        });

        RateLimiter::for('login', function (Request $request) {
            $key = ($request->input('email') ?? 'guest') . '|' . $request->ip();
            return Limit::perMinute(5)->by($key);
        });
    }
}
