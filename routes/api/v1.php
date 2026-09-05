<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use JOOservices\LaravelController\Http\Controllers\StatusController;

Route::get('/status', [StatusController::class, 'index']);
