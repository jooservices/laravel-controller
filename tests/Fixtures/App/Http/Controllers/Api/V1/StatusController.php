<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\JsonResponse;
use JOOservices\LaravelController\Http\Controllers\BaseApiController;
use UnexpectedValueException;

class StatusController extends BaseApiController
{
    /**
     * @throws UnexpectedValueException
     */
    public function index(): JsonResponse
    {
        return $this->success(['status' => 'operational'], 'System is running smoothly.');
    }
}
