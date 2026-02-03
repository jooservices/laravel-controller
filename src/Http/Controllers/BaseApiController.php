<?php

namespace JOOservices\LaravelController\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use JOOservices\LaravelController\Traits\HasApiResponses;

abstract class BaseApiController extends BaseController
{
    use AuthorizesRequests;
    use HasApiResponses;
    use ValidatesRequests;
}
