<?php

namespace App\Support;

use JOOservices\LaravelController\Contracts\ResponseFormatter;

class TestingResponseFormatter implements ResponseFormatter
{
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
        $payload = [
            'ok' => $response['success'],
            'status' => $response['code'],
            'message' => $response['message'],
            'result' => $response['data'],
            'issues' => $response['errors'],
            'diagnostics' => [
                'meta' => $response['meta'],
                'request_id' => $response['trace_id'],
            ],
        ];

        if ($response['warnings'] !== []) {
            $payload['diagnostics']['warnings'] = $response['warnings'];
        }

        return $payload;
    }
}
