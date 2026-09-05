<?php

declare(strict_types=1);

namespace JOOservices\LaravelController\Support;

use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use JOOservices\LaravelController\Contracts\StatusHealthCheck;
use Psr\SimpleCache\InvalidArgumentException as SimpleCacheInvalidArgumentException;
use RuntimeException;
use Throwable;

/**
 * Runs readiness health probes for the status endpoint.
 *
 * Built-in names: database, cache, queue.
 * Custom checks: class-strings implementing StatusHealthCheck (resolved via app()).
 */
class StatusHealthChecker
{
    /**
     * @param  array<int, string>  $checkNames
     * @return array<string, array{ok: bool, message?: string}>
     */
    public function run(array $checkNames, int $timeoutSeconds): array
    {
        $results = [];
        $deadline = $timeoutSeconds > 0 ? microtime(true) + $timeoutSeconds : 0.0;
        $prevTimeout = ini_get('default_socket_timeout');

        if ($timeoutSeconds > 0) {
            ini_set('default_socket_timeout', (string) $timeoutSeconds);
        }

        try {
            foreach ($checkNames as $name) {
                $resultKey = $this->resultKeyFor($name);

                if ($deadline > 0.0 && microtime(true) >= $deadline) {
                    $results[$resultKey] = ['ok' => false, 'message' => 'timeout'];

                    continue;
                }

                try {
                    $results[$resultKey] = $this->runOneCheck($name);
                } catch (Throwable $exception) {
                    $results[$resultKey] = [
                        'ok' => false,
                        'message' => $this->failureMessage($exception),
                    ];
                }
            }
        } finally {
            if (is_string($prevTimeout)) {
                ini_set('default_socket_timeout', $prevTimeout);
            }
        }

        return $results;
    }

    /**
     * @param  array<string, array{ok: bool, message?: string}>  $results
     */
    public function allPassed(array $results): bool
    {
        foreach ($results as $result) {
            if ($result['ok'] !== true) {
                return false;
            }
        }

        return true;
    }

    /**
     * @return array{ok: bool, message?: string}
     *
     * @throws BindingResolutionException
     * @throws RuntimeException
     * @throws SimpleCacheInvalidArgumentException
     */
    protected function runOneCheck(string $name): array
    {
        return match (strtolower($name)) {
            'database' => $this->checkDatabase(),
            'cache' => $this->checkCache(),
            'queue' => $this->checkQueue(),
            default => $this->runCustomCheck($name),
        };
    }

    protected function resultKeyFor(string $name): string
    {
        $lower = strtolower($name);

        if (in_array($lower, ['database', 'cache', 'queue'], true)) {
            return $lower;
        }

        if ($this->isCustomCheckClass($name)) {
            try {
                return $this->resolveCustomCheck($name)->name();
            } catch (Throwable) {
                return $name;
            }
        }

        return $name;
    }

    /**
     * @return array{ok: bool, message?: string}
     *
     * @throws BindingResolutionException
     * @throws RuntimeException
     */
    protected function runCustomCheck(string $name): array
    {
        if (! $this->isCustomCheckClass($name)) {
            return ['ok' => false, 'message' => 'unknown check'];
        }

        return $this->resolveCustomCheck($name)->check();
    }

    protected function isCustomCheckClass(string $name): bool
    {
        return class_exists($name) && is_a($name, StatusHealthCheck::class, true);
    }

    /**
     * @throws BindingResolutionException
     * @throws RuntimeException
     */
    protected function resolveCustomCheck(string $name): StatusHealthCheck
    {
        $check = app($name);

        if (! $check instanceof StatusHealthCheck) {
            throw new RuntimeException(sprintf(
                'Health check [%s] must implement %s.',
                $name,
                StatusHealthCheck::class,
            ));
        }

        return $check;
    }

    /**
     * @return array{ok: bool, message?: string}
     *
     * @throws RuntimeException
     */
    protected function checkDatabase(): array
    {
        DB::connection()->getPdo();

        return ['ok' => true];
    }

    /**
     * @return array{ok: bool, message?: string}
     *
     * @throws SimpleCacheInvalidArgumentException
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
     *
     * @throws RuntimeException
     */
    protected function checkQueue(): array
    {
        Queue::connection()->size();

        return ['ok' => true];
    }

    protected function failureMessage(Throwable $exception): string
    {
        return config('app.debug', false) === true ? $exception->getMessage() : 'check failed';
    }
}
