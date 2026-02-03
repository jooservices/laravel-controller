<?php

use Illuminate\Support\Facades\Route;
use JOOservices\LaravelController\Http\Controllers\StatusController;

Route::get('/status', [StatusController::class, 'index']);
