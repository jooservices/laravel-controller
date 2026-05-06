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

        $configuredStatus = config('laravel-controller.status', []);
        $statusConfig = is_array($configuredStatus)
            ? $this->normalizeStatusConfig($configuredStatus)
            : [];

        if (($statusConfig['include_version'] ?? false) === true) {
            $data['version'] = $this->appVersion();
        }

        if (($statusConfig['include_environment'] ?? false) === true) {
            $data['environment'] = app()->environment();
        }

        if (($statusConfig['include_maintenance'] ?? false) === true) {
            $data['maintenance'] = app()->isDownForMaintenance();
        }

        $checks = $this->configuredChecks($statusConfig);
        $timeoutSeconds = $this->configuredTimeoutSeconds($statusConfig);
        if ($checks !== []) {
            $data['checks'] = $this->runHealthChecks($checks, $timeoutSeconds);
        }

        return $this->success($data);
    }

    /**
     * @param  array<mixed>  $statusConfig
     * @return array<string, mixed>
     */
    protected function normalizeStatusConfig(array $statusConfig): array
    {
        $normalized = [];

        foreach ($statusConfig as $key => $value) {
            $normalized[(string) $key] = $value;
        }

        return $normalized;
    }

    /**
     * @param  array<string, mixed>  $statusConfig
     * @return array<int, string>
     */
    protected function configuredChecks(array $statusConfig): array
    {
        $checks = $statusConfig['checks'] ?? [];

        if (! is_array($checks)) {
            return [];
        }

        return array_values(array_filter(
            $checks,
            static fn (mixed $check): bool => is_string($check) && $check !== ''
        ));
    }

    /**
     * @param  array<string, mixed>  $statusConfig
     */
    protected function configuredTimeoutSeconds(array $statusConfig): int
    {
        $timeout = $statusConfig['checks_timeout_seconds'] ?? 5;

        if (is_int($timeout)) {
            return $timeout;
        }

        if (is_string($timeout) && ctype_digit($timeout)) {
            return (int) $timeout;
        }

        return 5;
    }

    /**
     * Run configured health checks and return results. Each check runs with optional timeout.
     * Note: The timeout only prevents starting new checks after the deadline; a single
     * blocking check (e.g. DB connect) can still hang the request until it completes.
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
                $results[$name] = [
                    'ok' => false,
                    'message' => $this->healthCheckFailureMessage($e),
                ];
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

    /**
     * Return a safe failure message for health checks. Avoids leaking internal details
     * (e.g. DB credentials, paths) unless app.debug is true.
     */
    protected function healthCheckFailureMessage(Throwable $exception): string
    {
        return config('app.debug', false) === true ? $exception->getMessage() : 'check failed';
    }
}
