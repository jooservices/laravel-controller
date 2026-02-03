# User Guide

## Introduction

The **Laravel Controller** package provides a standardized foundation for building API-first Laravel applications. It simplifies response handling, pagination, and error management, allowing you to focus on business logic.

## Installation

```bash
composer require jooservices/laravel-controller
```

### Publishing Configuration

To customize the package behavior (e.g., response keys, default routes), publish the configuration file:

```bash
php artisan vendor:publish --provider="JOOservices\LaravelController\Providers\LaravelControllerServiceProvider" --tag="config"
```

## Core Concepts

### BaseApiController

Your API controllers should extend `JOOservices\LaravelController\Http\Controllers\BaseApiController`. This class provides access to the helper traits and response methods.

### Standardized JSON Response

All responses follow a consistent envelope structure defined in `config/laravel-controller.php`. By default:

```json
{
    "success": true,
    "code": 200,
    "message": "Operation successful",
    "data": { ... },
    "errors": null,
    "meta": { ... },
    "trace_id": "unique-trace-id"
}
```

### Auto-Discovery Routes

The package can automatically map your versioned route files. If you place a file named `v1.php` in `routes/api/`, the package will attempt to map it to `api/v1` prefix and `App\Http\Controllers\Api\V1` namespace.

> **Note**: This feature checks `routes/api/*.php` in your host application.

## Usage

### Returning Data

Use `respondWithItem` or `respondWithCollection` to transform models using API Resources.

```php
public function show(User $user)
{
    return $this->respondWithItem($user, new UserResource($user));
}
```

### Pagination

Use `respondWithPagination` to automatically format paginated results.

```php
public function index()
{
    $users = User::paginate(20);
    return $this->respondWithPagination($users, UserResource::class);
}
```

### Error Handling

Return standard errors easily:

```php
if (! $user->isAdmin()) {
    return $this->errorForbidden('You do not have access.');
}
```

Available methods:
- `errorNotFound($message)` (404)
- `errorBadRequest($message)` (400)
- `errorForbidden($message)` (403)
- `errorInternal($message)` (500)
- `errorUnauthorized($message)` (401)
