# Configuration Recipes

## Custom response keys

```php
'keys' => [
    'success' => 'is_success',
    'data' => 'payload',
],
```

## 204 envelope support

```php
'envelope_204' => true,
```

## Custom response formatter

```php
'response_formatter' => App\Support\ApiResponseFormatter::class,
```

```php
<?php

namespace App\Support;

use JOOservices\LaravelController\Contracts\ResponseFormatter;

class ApiResponseFormatter implements ResponseFormatter
{
    public function format(array $response): array
    {
        return [
            'ok' => $response['success'],
            'status' => $response['code'],
            'payload' => $response['data'],
            'error' => $response['errors'],
            'request_id' => $response['trace_id'],
        ];
    }
}
```

## First validation error as top-level message

```php
'validation' => [
    'message' => 'first',
],
```

## Default item links

```php
'item_links' => true,
'item_links_default' => [
    'index' => '/api/v1/resources',
],
```