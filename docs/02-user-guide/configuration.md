# Configuration

Publish the config file:

```bash
php artisan vendor:publish --provider="JOOservices\LaravelController\Providers\LaravelControllerServiceProvider" --tag="config"
```

Important options:

## Response Keys

Override top-level keys if your frontend expects a different envelope schema.

```php
'keys' => [
    'data' => 'payload',
    'trace_id' => 'trace_id',
],
```

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
    'checks' => ['database', 'cache', 'queue'],
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
