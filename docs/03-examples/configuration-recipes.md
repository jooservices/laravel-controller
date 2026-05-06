# Configuration Recipes

## Custom Response Keys

```php
'keys' => [
    'success' => 'is_success',
    'data' => 'payload',
],
```

## Custom Trace Header

```php
'trace_id' => [
    'header' => 'X-Request-ID',
],
```

## 204 Envelope Support

```php
'envelope_204' => true,
```

## Custom Response Formatter

```php
<?php

namespace App\Support;

use JOOservices\LaravelController\Contracts\ResponseFormatter;

final class ApiResponseFormatter implements ResponseFormatter
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

## DTO Or Data Object Input

DTOs may be passed as response data when they are `Arrayable`, `JsonSerializable`, or expose `toArray()`.

```php
return $this->respondWithData(
    data: $summary,
    message: 'Summary generated successfully.',
);
```

This does not replace Laravel Resources for presentation.
