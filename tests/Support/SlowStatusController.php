<?php

declare(strict_types=1);

namespace JOOservices\LaravelController\Tests\Support;

use JOOservices\LaravelController\Http\Controllers\StatusController;
use Psr\SimpleCache\InvalidArgumentException as SimpleCacheInvalidArgumentException;
use RuntimeException;

/**
 * Test double that delays one named check so timeout branches can be exercised.
 */
final class SlowStatusController extends StatusController
{
    /**
     * @return array{ok: bool, message?: string}
     *
     * @throws RuntimeException
     * @throws SimpleCacheInvalidArgumentException
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
