# Problem Details (RFC 7807)

v4 can emit errors as [RFC 7807](https://www.rfc-editor.org/rfc/rfc7807) Problem Details with `Content-Type: application/problem+json`.

Success responses keep the JOOservices envelope and `application/json`.

## Enable for all errors

```php
// config/laravel-controller.php
'response_profile' => 'problem+json',
```

When `response_formatter` is set, that class wins over the profile.

## Per-response helper

```php
return $this->respondWithProblem(
    title: 'Not Found',
    status: 404,
    detail: 'User 42 does not exist.',
    type: 'https://example.com/problems/user-not-found', // optional
    errors: null,
);
```

Payload shape:

```json
{
  "type": "https://jooservices.dev/problems/http-404",
  "title": "Not Found",
  "status": 404,
  "detail": "User 42 does not exist.",
  "instance": "/api/v1/users/42",
  "trace_id": "..."
}
```

Optional extensions: `errors`, `trace_id`. Default `type` is `https://jooservices.dev/problems/http-{status}` when omitted.

## Custom formatter

`JOOservices\LaravelController\Formatters\ProblemDetailsFormatter` implements `ResponseFormatter`. You may set:

```php
'response_formatter' => JOOservices\LaravelController\Formatters\ProblemDetailsFormatter::class,
```

See also [OpenAPI contract](./openapi-contract.md) schema `ApiProblemDetails`.
