<?php

namespace App\Providers;

use App\Models\Activity;
use App\Models\Housing;
use App\Models\Travel;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

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

        // Register policies
        Gate::policy(Travel::class, \App\Policies\TravelPolicy::class);
        Gate::policy(Activity::class, \App\Policies\ActivityPolicy::class);
        Gate::policy(Housing::class, \App\Policies\HousingPolicy::class);
    }
}
