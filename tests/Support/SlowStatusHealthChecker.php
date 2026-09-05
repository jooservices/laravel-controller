<?php

declare(strict_types=1);

namespace JOOservices\LaravelController\Tests\Support;

use Illuminate\Contracts\Container\BindingResolutionException;
use JOOservices\LaravelController\Support\StatusHealthChecker;
use Psr\SimpleCache\InvalidArgumentException as SimpleCacheInvalidArgumentException;
use Random\RandomException;
use RuntimeException;

/**
 * Test double that delays one named check so timeout branches can be exercised.
 */
final class SlowStatusHealthChecker extends StatusHealthChecker
{
    /**
     * @return array{ok: bool, message?: string}
     *
     * @throws BindingResolutionException
     * @throws RuntimeException
     * @throws SimpleCacheInvalidArgumentException
     * @throws RandomException
     */
    protected function runOneCheck(string $name): array
    {
        if ($name === 'slow') {
            usleep(1_100_000);

            return ['ok' => true];
        }

        return parent::runOneCheck($name);
    }
}
