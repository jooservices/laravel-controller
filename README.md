# Laravel Controller

A robust, standardized Base API Controller for Laravel applications using DTOs and FormRequests. This package provides a consistent way to handle API responses, pagination, and exceptions.

## Features

-   **Standardized Responses**: Consistent JSON structure for success and error states.
-   **Pagination Support**: Built-in helpers to format paginated results with meta data.
-   **Exception Handling**: Automated exception mapping to proper HTTP status codes.
-   **DTO Integration**: Designed to work seamlessly with Data Transfer Objects.
-   **API Resources**: Easy integration with Laravel's API Resources.

## Installation

Install the package via composer:

```bash
composer require jooservices/laravel-controller
```

## Quick Start

### 1. Extend the BaseApiController

Create your controller extending `JOOservices\LaravelController\Http\Controllers\BaseApiController`:

```php
namespace App\Http\Controllers\Api\V1;

use JOOservices\LaravelController\Http\Controllers\BaseApiController;
use Illuminate\Http\Request;

class UserController extends BaseApiController
{
    public function index()
    {
        $users = User::paginate();
        return $this->respondWithPagination($users, UserResource::class);
    }

    public function show($id)
    {
        $user = User::find($id);

        if (!$user) {
            return $this->errorNotFound('User not found');
        }

        return $this->respondWithItem($user, new UserResource($user));
    }
}
```

### 2. Standardized Responses

The package provides several helper methods for returning responses:

-   `respondWithItem($item, $resource)`
-   `respondWithCollection($collection, $resourceClass)`
-   `respondWithPagination($paginator, $resourceClass)`
-   `respondCreated($data = null)`
-   `respondNoContent()`
-   `errorNotFound($message)`
-   `errorBadRequest($message)`
-   `errorForbidden($message)`
-   `errorInternal($message)`

## Configuration

Publish the configuration file to customize behavior:

```bash
php artisan vendor:publish --provider="JOOservices\LaravelController\Providers\LaravelControllerServiceProvider" --tag="config"
```

This will create `config/laravel-controller.php`.

### Routing

By default, the package may register some utility routes (e.g., `/api/v1/status`). You can disable these or change the prefix in the configuration file.

```php
// config/laravel-controller.php
return [
    'routes' => [
        'enabled' => true,
        'prefix' => 'api/v1',
    ],
];
```

## Documentation

For more detailed information, please refer to the documentation:

-   [User Guide](docs/user-guide.md)
-   [Examples](docs/examples.md)
-   [Developer Guide](docs/developer-guide.md)

## License

The MIT License (MIT). Please see [License File](LICENSE) for more information.
