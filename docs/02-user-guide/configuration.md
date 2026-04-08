# Configuration

The package configuration lives in `config/laravel-controller.php` after publishing.

Important options:

## Response keys

Override top-level keys if your frontend expects a different envelope schema.

```php
'keys' => [
    'data' => 'payload',
    'trace_id' => 'trace_id',
],
```

## Custom response formatter

Provide a formatter class when key remapping is not enough and you need a different envelope shape.

```php
'response_formatter' => App\Support\ApiResponseFormatter::class,
```

The class must implement `JOOservices\LaravelController\Contracts\ResponseFormatter` and return the full payload array. The package still controls the HTTP status code; your formatter controls only the JSON body.

## Routes

```php
'routes' => [
    'enabled' => true,
    'prefix' => 'api/v1',
],
```

## Status metadata

```php
'status' => [
    'include_version' => true,
    'include_environment' => true,
    'include_maintenance' => true,
    'checks' => ['database', 'cache', 'queue'],
    'checks_timeout_seconds' => 5,
],
```

## Other commonly used options

- `envelope_204`
- `use_translations`
- `validation.message`
- `success_codes`
- `item_links`
- `item_links_default`
- `pagination_links`