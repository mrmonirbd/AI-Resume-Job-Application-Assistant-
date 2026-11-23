<?php

namespace App\Providers;

use App\Services\AI\AIProviderInterface;
use App\Services\AI\DeepSeekProvider;
use App\Services\LegacyLoginViewFinder;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(AIProviderInterface::class, DeepSeekProvider::class);
        $this->app->singleton('view.finder', fn ($app): LegacyLoginViewFinder => new LegacyLoginViewFinder($app['files'], $app['config']['view.paths']));
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Vite::createAssetPathsUsing(function (string $path, ?bool $secure): string {
            $url = asset($path, $secure);

            if (! str_ends_with($path, '.css') || ! is_file(public_path($path))) {
                return $url;
            }

            return $url.'?v='.substr(hash_file('sha256', public_path($path)), 0, 12);
        });

        RateLimiter::for('ai', function (Request $request) {
            return Limit::perMinute(12)->by($request->user()?->id ?: $request->ip());
        });

        RateLimiter::for('login', function (Request $request) {
            return Limit::perMinute(5)->by(Str::lower((string) $request->input('email')).'|'.$request->ip());
        });

        Gate::define('view-admin', fn ($user) => (bool) $user->is_admin);
    }
}
