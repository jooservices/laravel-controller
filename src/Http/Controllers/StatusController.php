<?php

namespace JOOservices\LaravelController\Http\Controllers;

use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Illuminate\Foundation\Application;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Throwable;

class StatusController extends BaseApiController
{
    /**
     * Check API Status.
     *
     * Optionally includes version, environment, maintenance flag, and health checks (database, cache, queue).
     */
    public function index(): JsonResponse
    {
        $data = [
            'status' => 'ok',
            'message' => 'API is running',
            'timestamp' => now()->toIso8601String(),
        ];

        $statusConfig = (array) config('laravel-controller.status', []);

        if (! empty($statusConfig['include_version'])) {
            $data['version'] = $this->appVersion();
        }

        if (! empty($statusConfig['include_environment'])) {
            $data['environment'] = app()->environment();
        }

        if (! empty($statusConfig['include_maintenance'])) {
            $data['maintenance'] = app()->isDownForMaintenance();
        }

        $checks = $statusConfig['checks'] ?? [];
        $timeoutSeconds = (int) ($statusConfig['checks_timeout_seconds'] ?? 5);
        if ($checks !== []) {
            $data['checks'] = $this->runHealthChecks($checks, $timeoutSeconds);
        }

        return $this->success($data);
    }

    /**
     * Run configured health checks and return results. Each check runs with optional timeout.
     *
     * @param  array<int, string>  $checkNames  e.g. ['database', 'cache', 'queue']
     * @return array<string, array{ok: bool, message?: string}>
     */
    protected function runHealthChecks(array $checkNames, int $timeoutSeconds): array
    {
        $results = [];
        $deadline = $timeoutSeconds > 0 ? microtime(true) + $timeoutSeconds : 0;

        foreach ($checkNames as $name) {
            if ($deadline > 0 && microtime(true) >= $deadline) {
                $results[$name] = ['ok' => false, 'message' => 'timeout'];

                continue;
            }

            try {
                $results[$name] = $this->runOneCheck($name);
            } catch (Throwable $e) {
                $results[$name] = ['ok' => false, 'message' => $e->getMessage()];
            }
        }

        return $results;
    }

    /**
     * Run a single health check by name.
     *
     * @return array{ok: bool, message?: string}
     */
    protected function runOneCheck(string $name): array
    {
        return match (strtolower($name)) {
            'database' => $this->checkDatabase(),
            'cache' => $this->checkCache(),
            'queue' => $this->checkQueue(),
            default => ['ok' => false, 'message' => 'unknown check'],
        };
    }

    /**
     * @return array{ok: bool, message?: string}
     */
    protected function checkDatabase(): array
    {
        DB::connection()->getPdo();

        return ['ok' => true];
    }

    /**
     * @return array{ok: bool, message?: string}
     */
    protected function checkCache(): array
    {
        $key = 'laravel_controller_health';
        $cache = app(CacheRepository::class);
        $cache->put($key, 1, 10);
        if ($cache->get($key) !== 1) {
            return ['ok' => false, 'message' => 'read/write failed'];
        }

        return ['ok' => true];
    }

    /**
     * @return array{ok: bool, message?: string}
     */
    protected function checkQueue(): array
    {
        Queue::connection()->size();

        return ['ok' => true];
    }

    /**
     * Get application version (config app.version or Laravel version).
     */
    protected function appVersion(): string
    {
        $version = config('app.version', Application::VERSION);

        return is_string($version) ? $version : Application::VERSION;
    }
}
