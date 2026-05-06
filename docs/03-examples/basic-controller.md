# Basic Controller

This example follows the recommended application flow:

```text
Request -> Controller -> FormRequest -> Service -> Repository -> Model
```

```php
<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\ProductIndexRequest;
use App\Http\Requests\ProductStoreRequest;
use App\Http\Resources\ProductResource;
use App\Services\ProductService;
use Illuminate\Http\JsonResponse;
use JOOservices\LaravelController\Http\Controllers\BaseApiController;

final class ProductController extends BaseApiController
{
    public function index(ProductIndexRequest $request, ProductService $products): JsonResponse
    {
        return $this->respondWithPagination(
            paginator: $products->paginate($request->validated()),
            resourceClass: ProductResource::class,
            message: 'Products retrieved successfully.',
        );
    }

    public function store(ProductStoreRequest $request, ProductService $products): JsonResponse
    {
        return $this->respondWithResource(
            resource: new ProductResource($products->create($request->validated())),
            message: 'Product created successfully.',
            code: 201,
        );
    }
}
```
