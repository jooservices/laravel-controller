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

## Diagnostics

```bash
php artisan laravel-controller:doctor
php artisan laravel-controller:doctor --json
```
