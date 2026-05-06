# Quick Start

Extend `JOOservices\LaravelController\Http\Controllers\BaseApiController` in API controllers and keep application logic in services.

```php
<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\UserIndexRequest;
use App\Http\Resources\UserResource;
use App\Services\UserService;
use Illuminate\Http\JsonResponse;
use JOOservices\LaravelController\Http\Controllers\BaseApiController;

final class UserController extends BaseApiController
{
    public function index(UserIndexRequest $request, UserService $users): JsonResponse
    {
        return $this->respondWithPagination(
            paginator: $users->paginate($request->validated()),
            resourceClass: UserResource::class,
            message: 'Users retrieved successfully.',
        );
    }

    public function show(UserShowRequest $request, UserService $users): JsonResponse
    {
        return $this->respondWithResource(
            resource: new UserResource($users->findForDisplay($request->validated('id'))),
            message: 'User retrieved successfully.',
        );
    }
}
```

The resource is still responsible for presentation:

```php
<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->resource->id,
            'name' => $this->resource->name,
        ];
    }
}
```

Run diagnostics after installation:

```bash
php artisan laravel-controller:doctor
php artisan laravel-controller:doctor --json
```
