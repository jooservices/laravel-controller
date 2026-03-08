# Examples

## Basic CRUD Controller

Complete example of a Product controller using the package response methods.

```php
namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\ProductRequest;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use JOOservices\LaravelController\Http\Controllers\BaseApiController;

class ProductController extends BaseApiController
{
    public function index()
    {
        $products = Product::paginate(15);

        return $this->respondWithPagination($products, ProductResource::class);
    }

    public function store(ProductRequest $request)
    {
        $product = Product::create($request->validated());

        return $this->created([
            'id' => $product->id,
            'message' => 'Product created successfully',
        ]);
    }

    public function show($id)
    {
        $product = Product::find($id);

        if (! $product) {
            return $this->notFound("Product with ID {$id} not found.");
        }

        return $this->respondWithItem($product, ProductResource::class);
    }

    public function update(ProductRequest $request, $id)
    {
        $product = Product::find($id);

        if (! $product) {
            return $this->notFound('Product not found');
        }

        $product->update($request->validated());

        return $this->respondWithItem($product, ProductResource::class);
    }

    public function destroy($id)
    {
        $product = Product::find($id);

        if (! $product) {
            return $this->notFound('Product not found');
        }

        $product->delete();

        return $this->noContent();
    }
}
```

## Success with warnings

Attach non-fatal warnings (e.g. deprecation or partial success) to a success response:

```php
return $this->success($data, 'Success', 200, [], [
    'deprecated' => 'This endpoint will be removed in v2. Use /v2/products instead.',
]);
```

## Customizing response keys

If your frontend expects different keys (e.g. `payload` instead of `data`), override them in `config/laravel-controller.php`:

```php
return [
    'keys' => [
        'success' => 'is_success',
        'data' => 'payload',
        // ...
    ],
];
```

## Disabling default routes

To disable the package status route (e.g. `/api/v1/status`):

```php
'routes' => [
    'enabled' => false,
],
```

## Status endpoint options

Control what the status endpoint returns:

```php
'status' => [
    'include_version' => true,
    'include_environment' => true,
    'include_maintenance' => true,
],
```

Set `app.version` in your app config if you want a custom version string instead of the Laravel framework version.

## Cursor and offset pagination

Cursor-based (e.g. for infinite scroll or keyset pagination):

```php
$items = User::where('id', '>', $cursor)->orderBy('id')->limit(20)->get();
$nextCursor = $items->isNotEmpty() ? $items->last()->id : null;
return $this->respondWithCursorPagination($items, $cursor, $nextCursor, $items->count() === 20, UserResource::class);
```

Offset-based:

```php
$offset = (int) $request->get('offset', 0);
$limit = min(50, (int) $request->get('limit', 20));
$items = User::offset($offset)->limit($limit + 1)->get();
$hasMore = $items->count() > $limit;
if ($hasMore) {
    $items = $items->take($limit);
}
$total = User::count();
return $this->respondWithOffsetPagination($items, $offset, $limit, $total, UserResource::class);
```

## Item links (HAL-style)

Add `self` and `index` links to a single resource response:

```php
return $this->respondWithItem($user, UserResource::class, [
    'self' => route('api.v1.users.show', $user),
    'index' => route('api.v1.users.index'),
]);
```

## 204 with envelope

When `envelope_204` is `true` in config, `noContent()` returns the same envelope as other responses (with `data: null`, `trace_id`, etc.) so clients always get a consistent shape.

## Validation message (first error)

In `config/laravel-controller.php`, set the validation top-level message to the first validation error:

```php
'validation' => [
    'message' => 'first',
],
```

## Rate limiting (429)

Return 429 using `Retry-After` from the request (e.g. set by throttle middleware):

```php
return $this->respondTooManyRequestsFromRequest('Too Many Requests', 60);
```
