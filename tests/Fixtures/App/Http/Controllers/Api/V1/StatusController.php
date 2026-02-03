<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\JsonResponse;
use JOOservices\LaravelController\Http\Controllers\BaseApiController;

class StatusController extends BaseApiController
{
    public function index(): JsonResponse
    {
        return $this->success(['status' => 'operational'], 'System is running smoothly.');
    }
}
