<?php

declare(strict_types=1);

namespace JOOservices\LaravelController\Tests\Support;

use JOOservices\LaravelController\Contracts\StatusHealthCheck;

final class FakeStatusHealthCheck implements StatusHealthCheck
{
    public function name(): string
    {
        return 'fake_probe';
    }

    /**
     * @return array{ok: bool, message?: string}
     */
    public function check(): array
    {
        return ['ok' => true];
    }
}
