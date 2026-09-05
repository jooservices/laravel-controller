# Configuration

Publish the config file:

```bash
php artisan vendor:publish --provider="JOOservices\LaravelController\Providers\LaravelControllerServiceProvider" --tag="config"
```

Important options:

## Response Profile

```php
'response_profile' => 'envelope', // or 'problem+json'
```

With `problem+json`, error responses use RFC 7807 and `application/problem+json`. Success responses keep the default envelope. An explicit `response_formatter` still wins when set.

## Response Keys

Override top-level keys if your frontend expects a different envelope schema.

```php
'keys' => [
    'data' => 'payload',
    'trace_id' => 'trace_id',
],
```

## Meta Headers (echo only)

```php
'meta_headers' => [
    'enabled' => true,
    'idempotency' => 'Idempotency-Key',
    'rate_limit' => [
        'limit' => 'X-RateLimit-Limit',
        'remaining' => 'X-RateLimit-Remaining',
        'reset' => 'X-RateLimit-Reset',
    ],
    'retry_after' => 'Retry-After',
],
```

Present request headers are copied into `meta` (`idempotency_key`, `rate_limit`, `retry_after`). The package does **not** store idempotency keys or enforce rate limits.

## Trace ID

```php
'trace_id' => [
    'header' => 'X-Trace-ID',
],
```

The package reads this request header before generating a UUID fallback.

## Custom Response Formatter

```php
'response_formatter' => App\Support\ApiResponseFormatter::class,
```

The class must implement `JOOservices\LaravelController\Contracts\ResponseFormatter`.

## Status Endpoint

```php
'status' => [
    'include_version' => true,
    'include_environment' => true,
    'include_maintenance' => true,
    'checks' => ['database', 'cache', 'queue'], // and/or StatusHealthCheck class-strings
    'checks_timeout_seconds' => 5,
],
```

`checks_timeout_seconds` may be an integer or a digit string. Runtime status
checks normalize both forms, and the doctor command reports invalid values.

## Routes

```php
'routes' => [
    'enabled' => true,
    'prefix' => 'api/v1',
    'auto_map_host_routes' => true,
],
```

Set `routes.enabled` to `false` to disable package routes such as `/status`.
Set `routes.auto_map_host_routes` to `false` when the host application should
own all API route registration itself.

## Diagnostics

```bash
php artisan laravel-controller:doctor
php artisan laravel-controller:doctor --json
```
