# User Guide

## Introduction

The **Laravel Controller** package provides a standardized foundation for building API-first Laravel applications. It simplifies response handling, pagination, and error management, allowing you to focus on business logic.

## Installation

```bash
composer require jooservices/laravel-controller
```

### Publishing Configuration

To customize the package behavior (e.g. response keys, default routes, status endpoint, pagination links), publish the configuration file:

```bash
php artisan vendor:publish --provider="JOOservices\LaravelController\Providers\LaravelControllerServiceProvider" --tag="config"
```

## Core Concepts

### BaseApiController

Your API controllers should extend `JOOservices\LaravelController\Http\Controllers\BaseApiController`. This class provides access to the response helpers and exception handling.

### Standardized JSON Response

All responses follow a consistent envelope. The keys are configurable in `config/laravel-controller.php` (see [Configuration](#configuration)). By default:

```json
{
    "success": true,
    "code": 200,
    "message": "Operation successful",
    "data": { ... },
    "meta": { ... },
    "errors": null,
    "trace_id": "unique-trace-id"
}
```

Optional `warnings` can appear on success responses (e.g. deprecation notices, partial success messages).

### Trace ID

Clients can send an `X-Trace-ID` header with each request. The same value is returned in the response (or a new UUID is generated if not sent). Use it for request correlation, logging, and support.

### Auto-Discovery Routes

The package can automatically map your versioned route files. If you place a file named `v1.php` in `routes/api/`, the package will attempt to map it to `api/v1` prefix and `App\Http\Controllers\Api\V1` namespace.

> **Note**: This feature checks `routes/api/*.php` in your host application.

## Usage

### Returning Data

Use **canonical** methods or the convenience helpers:

- **Single item with API Resource:** `respondWithItem($model, ResourceClass::class)` — pass the resource **class** (string), not an instance.
- **Collection with API Resource:** `respondWithCollection($items, ResourceClass::class)`.
- **Generic success:** `success($data, $message, $code, $meta, $warnings)`.

```php
public function show(User $user)
{
    return $this->respondWithItem($user, UserResource::class);
}
```

### Pagination

Use `respondWithPagination($paginator, ResourceClass::class)` to format paginated results. When `pagination_links` is enabled in config, `meta.links` includes `first`, `last`, `prev`, `next` URLs.

```php
public function index()
{
    $users = User::paginate(20);
    return $this->respondWithPagination($users, UserResource::class);
}
```

For cursor-based or offset-based lists, use `respondWithCursorPagination($items, $cursor, $nextCursor, $hasMore, ResourceClass::class)` or `respondWithOffsetPagination($items, $offset, $limit, $total, ResourceClass::class)`. These use the same envelope with `meta.cursor` / `meta.next_cursor` / `meta.has_more` or `meta.offset` / `meta.limit` / `meta.total` / `meta.has_more`.

### Rate limiting (429)

Use `tooManyRequests($message, $retryAfter)` to return a 429 with a consistent envelope and `Retry-After` header. To derive `retry_after` from Laravel’s throttle (or a `Retry-After` header set by your middleware), use:

```php
return $this->respondTooManyRequestsFromRequest('Too Many Requests', 60);
```

In your throttle middleware or exception handler, set the `Retry-After` header on the request (e.g. from the rate limiter’s availableAt()) before calling this, so the response reflects the correct retry time.

### Error Handling

Use the error methods:

```php
if (! $user->isAdmin()) {
    return $this->forbidden('You do not have access.');
}
```

| Method | HTTP |
|--------|------|
| `badRequest($message, $errors)` | 400 |
| `unauthorized($message)` | 401 |
| `forbidden($message)` | 403 |
| `notFound($message)` | 404 |
| `conflict($message, $errors)` | 409 |
| `gone($message)` | 410 |
| `unprocessable($message, $errors)` | 422 |
| `tooManyRequests($message, $retryAfter)` | 429 |
| `internalError($message)` | 500 |

### Error payload shape

- **Validation (422):** `errors` is an object mapping field names to arrays of messages, e.g. `{"email": ["The email field is required."]}`.
- **Other errors:** `errors` may be `null` or an object (e.g. 429 returns `{"retry_after": 60}`). Frontends should handle both validation (field → messages) and generic object shapes.

## Configuration

### Response keys

Default keys: `success`, `code`, `message`, `data`, `errors`, `meta`, `trace_id`, `warnings`. Override in `config/laravel-controller.php` under `keys` (e.g. set `data` to `payload` if your frontend expects it).

### Status endpoint

The `/api/v1/status` route (or your configured prefix) can include:

- `version` — from `config('app.version')` or Laravel version
- `environment` — from `app()->environment()`
- `maintenance` — from `app()->isDownForMaintenance()`

Toggle these in `config/laravel-controller.php` under `status`:

```php
'status' => [
    'include_version' => true,
    'include_environment' => true,
    'include_maintenance' => true,
],
```

### Pagination links

Set `pagination_links` to `true` (default) to add `meta.links` with `first`, `last`, `prev`, `next` URLs in paginated responses.

### 204 envelope, translations, validation message, success range

- **envelope_204**: When `true`, `noContent()` returns the same envelope as other responses (with `data: null`, `trace_id`, etc.) so clients always see a consistent shape.
- **use_translations**: When `true`, default messages (e.g. "Not Found", "Unauthorized") are resolved from `laravel-controller::messages.*`. Publish lang files with `--tag=laravel-controller-lang`.
- **validation.message**: For validation (422) responses, set a fixed string (e.g. `"Validation failed"`) or `"first"` to use the first validation error as the top-level `message`.
- **success_codes**: Default `null` (any 2xx is success). Set to e.g. `[200, 201]` so only those codes get `success: true` in the envelope.

### Status endpoint health checks

Add optional health checks to the status endpoint:

```php
'status' => [
    'include_version' => true,
    'include_environment' => true,
    'include_maintenance' => true,
    'checks' => ['database', 'cache', 'queue'],
    'checks_timeout_seconds' => 5,
],
```

Each check runs in sequence; results appear in `data.checks` as `{ "database": { "ok": true }, ... }`. Supported check names: `database`, `cache`, `queue`.

### Item links (HAL-style for single resource)

When `item_links` is `true`, `respondWithItem($item, ResourceClass::class, $links)` accepts an optional third argument `$links` (e.g. `['self' => url()->current(), 'index' => route('users.index')]`). Set `item_links_default` in config to add default links to every item response.

## Response reference

For the exact shape of every response type (success, 422, 429, 500, 204 with/without envelope), see [Response reference](response-reference.md).
