# Quick Start

Create a controller that extends `JOOservices\LaravelController\Http\Controllers\BaseApiController`.

```php
namespace App\Http\Controllers\Api\V1;

use App\Http\Resources\UserResource;
use App\Models\User;
use JOOservices\LaravelController\Http\Controllers\BaseApiController;

class UserController extends BaseApiController
{
    public function index()
    {
        return $this->respondWithPagination(User::paginate(), UserResource::class);
    }

    public function show(User $user)
    {
        return $this->respondWithItem($user, UserResource::class);
    }
}
```

For host applications using versioned route files, keep routes in `routes/api/v1.php` so the package can map them consistently.