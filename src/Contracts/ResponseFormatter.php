<?php

namespace JOOservices\LaravelController\Contracts;

interface ResponseFormatter
{
    /**
     * Format the normalized response context into the final JSON payload.
     *
     * @param  array{
     *     success: bool,
     *     code: int,
     *     message: string,
     *     data: mixed,
     *     meta: array<string, mixed>,
     *     errors: mixed,
     *     trace_id: string,
     *     warnings: array<int, string>|array<string, string>,
     *     keys: array<string, string>
     * }  $response
     * @return array<string, mixed>
     */
    public function format(array $response): array;
}
