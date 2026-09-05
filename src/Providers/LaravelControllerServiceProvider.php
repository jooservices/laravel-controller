<?php

declare(strict_types=1);

namespace JOOservices\LaravelController\Providers;

use BadMethodCallException;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;
use JOOservices\LaravelController\Console\Commands\LaravelControllerDoctorCommand;
use LogicException;
use RuntimeException;
use Symfony\Component\Finder\Exception\DirectoryNotFoundException;
use Symfony\Component\Finder\Finder;

class LaravelControllerServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     *
     * @throws BadMethodCallException
     * @throws BindingResolutionException
     * @throws DirectoryNotFoundException
     * @throws LogicException
     * @throws RuntimeException
     */
    public function boot(): void
    {
        $this->publishes([
            __DIR__ . '/../config/laravel-controller.php' => config_path('laravel-controller.php'),
        ], 'config');

        $this->loadTranslationsFrom(__DIR__ . '/../../resources/lang', 'laravel-controller');
        $this->publishes([
            __DIR__ . '/../../resources/lang' => lang_path('vendor/laravel-controller'),
        ], 'laravel-controller-lang');

        /** @var Router $router */
        $router = $this->app->make(Router::class);

        // Load Package Routes (if enabled)
        if (config('laravel-controller.routes.enabled', true) === true) {
            $prefix = config('laravel-controller.routes.prefix', 'api/v1');

            $router->group([
                'prefix' => $prefix,
                'middleware' => 'api',
            ], function () {
                $this->loadRoutesFrom(__DIR__ . '/../../routes/api/v1.php');
            });
        }

        if (config('laravel-controller.routes.auto_map_host_routes', true) === true) {
            $this->mapApiRoutes($router);
        }

        if ($this->app->runningInConsole()) {
            $this->commands([
                LaravelControllerDoctorCommand::class,
            ]);
        }
    }

    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/laravel-controller.php',
            'laravel-controller',
        );
    }

    /**
     * Map API routes automatically based on version files.
     *
     * @throws BadMethodCallException
     * @throws DirectoryNotFoundException
     * @throws LogicException
     * @throws RuntimeException
     */
    protected function mapApiRoutes(Router $router): void
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
            $namespaceVersion = strtoupper($version); // e.g., "V1"

            // We assume the User's controllers are in App\Http\Controllers\Api\{V1}
            // This is the variable part. The base App namespace could be configured, but 'App' is standard.
            $controllerNamespace = "App\\Http\\Controllers\\Api\\{$namespaceVersion}";

            $path = $file->getRealPath();
            if ($path === false) {
                continue;
            }

            $router->group([
                'prefix' => 'api/' . $version,
                'middleware' => 'api',
                'namespace' => $controllerNamespace,
            ], $path);
        }
    }
}
