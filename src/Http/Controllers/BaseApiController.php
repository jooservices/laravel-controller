<?php

declare(strict_types=1);

namespace JOOservices\LaravelController\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use JOOservices\LaravelController\Traits\HandlesApiExceptions;

abstract class BaseApiController extends BaseController
{
    use AuthorizesRequests;
    use HandlesApiExceptions;
    use ValidatesRequests;
}
