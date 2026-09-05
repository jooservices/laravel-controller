<?php

declare(strict_types=1);

namespace JOOservices\LaravelController\Tests\Support;

use JOOservices\LaravelController\Contracts\ResponseFormatter;

/**
 * Exists for class_exists checks but cannot be resolved from the container.
 */
abstract class UnresolvableResponseFormatter implements ResponseFormatter
{
}
