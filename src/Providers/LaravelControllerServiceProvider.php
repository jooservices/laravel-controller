<?php

namespace JOOservices\LaravelController\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Symfony\Component\Finder\Finder;

class LaravelControllerServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     *
     * @SuppressWarnings(PHPMD.StaticAccess)
     */
    public function boot(): void
    {
        $this->publishes([
            __DIR__ . '/../config/laravel-controller.php' => config_path('laravel-controller.php'),
        ], 'laravel-controller-config');

        // Load Package Routes (if enabled)
        if (config('laravel-controller.routes.enabled', true)) {
            $prefix = config('laravel-controller.routes.prefix', 'api/v1');

            Route::group([
                'prefix' => $prefix,
                'middleware' => 'api',
            ], function () {
                $this->loadRoutesFrom(__DIR__ . '/../../routes/api/v1.php');
            });
        }

        // Auto-map User's Routes (feature of this package)
        $this->mapApiRoutes();
    }

    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/laravel-controller.php',
            'laravel-controller'
        );
    }

    /**
     * Map API routes automatically based on version files.
     *
     * @SuppressWarnings(PHPMD.StaticAccess)
     */
    protected function mapApiRoutes(): void
    {
        // We look for routes in the HOST app's "routes/api" directory
        $routePath = base_path('routes/api');

        if (! is_dir($routePath)) {
            return;
        }

        $finder = new Finder();
        $finder->files()->in($routePath)->name('v*.php');

        foreach ($finder as $file) {
            $version = $file->getBasename('.php'); // e.g., "v1"
            $namespaceVersion = Str::upper($version); // e.g., "V1"

            // We assume the User's controllers are in App\Http\Controllers\Api\{V1}
            // This is the variable part. The base App namespace could be configured, but 'App' is standard.
            $controllerNamespace = "App\\Http\\Controllers\\Api\\{$namespaceVersion}";

            Route::prefix('api/' . $version)
                ->middleware('api')
                ->namespace($controllerNamespace)
                ->group($file->getRealPath());
        }
    }
}
