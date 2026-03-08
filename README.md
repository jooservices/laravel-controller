# Laravel Controller

A robust, standardized Base API Controller for Laravel applications using DTOs and FormRequests. This package provides a consistent way to handle API responses, pagination, and exceptions.

## Features

-   **Standardized Responses**: Consistent JSON structure for success and error states.
-   **Pagination Support**: Built-in helpers with optional HAL-style links (first, last, prev, next).
-   **Exception Handling**: Automated exception mapping to proper HTTP status codes.
-   **DTO Integration**: Designed to work seamlessly with Data Transfer Objects.
-   **API Resources**: Easy integration with Laravel's API Resources via `respondWithItem`, `respondWithCollection`, `respondWithPagination`.
-   **Trace ID**: Request correlation via `X-Trace-ID` header (sent by client or auto-generated).
-   **Optional 204 envelope**: Config `envelope_204` so `noContent()` returns the same envelope (with `trace_id`, etc.).
-   **Cursor/offset pagination**: `respondWithCursorPagination()` and `respondWithOffsetPagination()` with consistent `meta` shape.
-   **Status health checks**: Optional `status.checks` (e.g. `database`, `cache`, `queue`) for readiness/liveness.
-   **Configurable validation message**: Use a custom message or the first validation error for 422 responses.
-   **Rate limit helper**: `respondTooManyRequestsFromRequest()` to derive 429 from request/limiter.
-   **Localization**: Optional `use_translations` with publishable lang keys.
-   **Item links**: Optional HAL-style `meta.links` for single-item responses.
-   **Success range**: Config `success_codes` to control which HTTP codes get `success: true`.

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

use App\Http\Resources\UserResource;
use App\Models\User;
use JOOservices\LaravelController\Http\Controllers\BaseApiController;

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

        if (! $user) {
            return $this->notFound('User not found');
        }

        return $this->respondWithItem($user, UserResource::class);
    }
}
```

### 2. Response methods (canonical names)

| Method | HTTP | Description |
|--------|------|-------------|
| `success($data, $message, $code, $meta, $warnings)` | 200 | Success with optional meta and warnings |
| `created($data, $message)` | 201 | Resource created |
| `accepted($data, $message)` | 202 | Request accepted (e.g. async processing) |
| `noContent()` | 204 | No content |
| `badRequest($message, $errors)` | 400 | Bad request |
| `unauthorized($message)` | 401 | Unauthorized |
| `forbidden($message)` | 403 | Forbidden |
| `notFound($message)` | 404 | Not found |
| `conflict($message, $errors)` | 409 | Conflict (e.g. duplicate) |
| `gone($message)` | 410 | Resource gone / deprecated |
| `unprocessable($message, $errors)` | 422 | Validation errors |
| `tooManyRequests($message, $retryAfter)` | 429 | Rate limited |
| `internalError($message)` | 500 | Server error |

**Item/collection/pagination helpers:** `respondWithItem($item, ResourceClass::class)`, `respondWithCollection($items, ResourceClass::class)`, `respondWithPagination($paginator, ResourceClass::class)`. The method `paginated()` is deprecated; use `respondWithPagination()`.

## Response envelope and Trace ID

All JSON responses use the keys defined in `config/laravel-controller.php` (see [Configuration](#configuration)). Default shape:

```json
{
    "success": true,
    "code": 200,
    "message": "Success",
    "data": { ... },
    "meta": { ... },
    "errors": null,
    "trace_id": "unique-trace-id"
}
```

**Trace ID**: Clients can send an `X-Trace-ID` header; the same value (or a generated UUID if omitted) is returned in every response for correlation and support. Use it when reporting issues or tracing requests across services.

## Error payload shape

- **Validation (422)**: `errors` is an object (field → array of messages), e.g. `{"email": ["The email field is required."]}`.
- **Other errors**: `errors` may be `null` or an object with extra info (e.g. 429: `{"retry_after": 60}`). Your frontend should handle both array-style (validation) and object-style `errors`.

## Configuration

Publish the configuration file:

```bash
php artisan vendor:publish --provider="JOOservices\LaravelController\Providers\LaravelControllerServiceProvider" --tag="config"
```

This creates `config/laravel-controller.php`. Main options:

### Response keys

Override keys to match your frontend (e.g. `payload` instead of `data`):

```php
'keys' => [
    'success' => 'success',
    'code' => 'code',
    'message' => 'message',
    'data' => 'payload',   // example: frontend expects "payload"
    'errors' => 'errors',
    'meta' => 'meta',
    'trace_id' => 'trace_id',
    'warnings' => 'warnings',
],
```

### Routes

```php
'routes' => [
    'enabled' => true,
    'prefix' => 'api/v1',
],
```

### Status endpoint

Control what the `/api/v1/status` (or your prefix) endpoint returns:

```php
'status' => [
    'include_version' => true,
    'include_environment' => true,
    'include_maintenance' => true,
    'checks' => ['database', 'cache', 'queue'],  // optional health checks
    'checks_timeout_seconds' => 5,
],
```

When enabled, the response includes `version`, `environment`, and `maintenance` so clients can show “API under maintenance” or “please upgrade client”.

### Pagination links

When `pagination_links` is true (default), paginated responses include `meta.links` with `first`, `last`, `prev`, `next` URLs for HAL-style navigation.

```php
'pagination_links' => true,
```

For more options (envelope_204, use_translations, validation.message, success_codes, item_links, rate limiting), see [User Guide](docs/user-guide.md). For exact response shapes (success, 422, 429, 500, 204), see [Response reference](docs/response-reference.md).

## Documentation

-   [User Guide](docs/user-guide.md)
-   [Response reference](docs/response-reference.md)
-   [Examples](docs/examples.md)
-   [Developer Guide](docs/developer-guide.md)

## License

The MIT License (MIT). Please see [License File](LICENSE) for more information.
