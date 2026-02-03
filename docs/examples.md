# Examples

## Basic CRUD Controller

Here is a complete example of a Product controller.

```php
namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\V1\Controller; // Your base controller extending the package's BaseApiController
use App\Http\Requests\ProductRequest;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Http\Request;
use JOOservices\LaravelController\Http\Controllers\BaseApiController;

class ProductController extends BaseApiController
{
    public function index()
    {
        $products = Product::paginate(15);
        
        // Automatically formats with 'data' and 'meta' (pagination)
        return $this->respondWithPagination($products, ProductResource::class);
    }

    public function store(ProductRequest $request)
    {
        $product = Product::create($request->validated());
        
        return $this->respondCreated([
            'id' => $product->id,
            'message' => 'Product created successfully'
        ]);
    }

    public function show($id)
    {
        $product = Product::find($id);

        if (! $product) {
            return $this->errorNotFound("Product with ID {$id} not found.");
        }

        return $this->respondWithItem($product, new ProductResource($product));
    }

    public function update(ProductRequest $request, $id)
    {
        $product = Product::find($id);

        if (! $product) {
            return $this->errorNotFound();
        }

        $product->update($request->validated());

        return $this->respondWithItem($product, new ProductResource($product));
    }

    public function destroy($id)
    {
        $product = Product::find($id);

        if (! $product) {
            return $this->errorNotFound();
        }

        $product->delete();

        return $this->respondNoContent();
    }
}
```

## Customizing Response Keys

If your frontend team expects different keys (e.g., `payload` instead of `data`), you can change them in `config/laravel-controller.php`.

```php
// config/laravel-controller.php
return [
    'keys' => [
        'success' => 'is_success', // Changed from 'success'
        'data' => 'payload',       // Changed from 'data'
        // ...
    ],
];
```

## Disabling Default Routes

If you do NOT want the package to verify status at `/api/v1/status`:

```php
// config/laravel-controller.php
'routes' => [
    'enabled' => false,
],
```
