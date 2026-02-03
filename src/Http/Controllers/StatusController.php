<?php

namespace JOOservices\LaravelController\Http\Controllers;

use Illuminate\Http\JsonResponse;

class StatusController extends BaseApiController
{
    /**
     * Check API Status
     */
    public function index(): JsonResponse
    {
        return $this->success([
            'status' => 'ok',
            'message' => 'API is running',
            'timestamp' => now()->toIso8601String(),
        ]);
    }
}
