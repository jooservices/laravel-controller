<?php

declare(strict_types=1);

namespace JOOservices\LaravelController\Contracts;

/**
 * Pluggable readiness probe for the status endpoint.
 */
interface StatusHealthCheck
{
    /**
     * Stable machine-readable name used as the checks map key.
     */
    public function name(): string;

    /**
     * @return array{ok: bool, message?: string}
     */
    public function check(): array;
}
