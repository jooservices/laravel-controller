# Basic Controller

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
        return $this->respondWithPagination(Product::paginate(15), ProductResource::class);
    }

    public function store(ProductRequest $request)
    {
        $product = Product::create($request->validated());

        return $this->created(['id' => $product->id], 'Product created successfully');
    }

    public function show(Product $product)
    {
        return $this->respondWithItem($product, ProductResource::class);
    }
}
```