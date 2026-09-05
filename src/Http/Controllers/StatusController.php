<?php

declare(strict_types=1);

namespace JOOservices\LaravelController\Http\Controllers;

use Illuminate\Foundation\Application;
use Illuminate\Http\JsonResponse;
use JOOservices\LaravelController\Support\StatusHealthChecker;
use Symfony\Component\HttpFoundation\Response;
use UnexpectedValueException;

class StatusController extends BaseApiController
{
    public function __construct(
        protected StatusHealthChecker $healthChecker = new StatusHealthChecker(),
    ) {
    }

    /**
     * Liveness when no checks are configured (HTTP 200).
     * Readiness when checks are configured: any failed check returns HTTP 503.
     *
     * @throws UnexpectedValueException
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
        if ($checks === []) {
            return $this->success($data);
        }

        $timeoutSeconds = $this->configuredTimeoutSeconds($statusConfig);
        $results = $this->healthChecker->run($checks, $timeoutSeconds);
        $data['checks'] = $results;

        if (! $this->healthChecker->allPassed($results)) {
            $data['status'] = 'unavailable';
            $data['message'] = 'API readiness checks failed';

            return $this->success($data, $data['message'], Response::HTTP_SERVICE_UNAVAILABLE);
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
            static fn(mixed $check): bool => is_string($check) && $check !== '',
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

    protected function appVersion(): string
    {
        $version = config('app.version', Application::VERSION);

        return is_string($version) ? $version : Application::VERSION;
    }
}
