<?php

declare(strict_types=1);

namespace JOOservices\LaravelController\Formatters;

use JOOservices\LaravelController\Contracts\ResponseFormatter;
use JOOservices\LaravelController\OpenApi\EnvelopeContract;

/**
 * Formats error responses as RFC 7807 Problem Details.
 * Success responses keep the default JOOservices envelope shape.
 */
final class ProblemDetailsFormatter implements ResponseFormatter
{
    private const DEFAULT_TYPE_PREFIX = 'https://jooservices.dev/problems/http-';

    /**
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
    public function format(array $response): array
    {
        if ($response['success'] === true) {
            return $this->formatSuccessEnvelope($response);
        }

        return $this->formatProblemDetails($response);
    }

    /**
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
    private function formatSuccessEnvelope(array $response): array
    {
        $keys = $response['keys'];
        $payload = [
            $this->key($keys, 'success') => true,
            $this->key($keys, 'code') => $response['code'],
            $this->key($keys, 'message') => $response['message'],
            $this->key($keys, 'data') => $response['data'],
            $this->key($keys, 'meta') => (object) $response['meta'],
            $this->key($keys, 'errors') => $response['errors'],
            $this->key($keys, 'trace_id') => $response['trace_id'],
        ];

        if ($response['warnings'] !== []) {
            $payload[$this->key($keys, 'warnings')] = $response['warnings'];
        }

        return $payload;
    }

    /**
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
    private function formatProblemDetails(array $response): array
    {
        $meta = $response['meta'];
        $status = $response['code'];

        $type = $meta['problem_type'] ?? null;
        if (! is_string($type) || $type === '') {
            $type = self::DEFAULT_TYPE_PREFIX . $status;
        }

        $payload = [
            'type' => $type,
            'title' => $response['message'],
            'status' => $status,
        ];

        $detail = $meta['problem_detail'] ?? null;
        if (is_string($detail) && $detail !== '') {
            $payload['detail'] = $detail;
        }

        $instance = $meta['problem_instance'] ?? null;
        if (is_string($instance) && $instance !== '') {
            $payload['instance'] = $instance;
        }

        if ($response['errors'] !== null) {
            $payload['errors'] = $response['errors'];
        }

        if ($response['trace_id'] !== '') {
            $payload['trace_id'] = $response['trace_id'];
        }

        return $payload;
    }

    /**
     * @param  array<string, string>  $keys
     */
    private function key(array $keys, string $name): string
    {
        $value = $keys[$name] ?? $name;

        return $value !== '' ? $value : $name;
    }

    public static function contentType(): string
    {
        return EnvelopeContract::CONTENT_TYPE_PROBLEM_JSON;
    }
}
