# Response Envelopes

All package helpers produce a consistent JSON contract based on `config/laravel-controller.php`.

Default envelope shape:

```json
{
  "success": true,
  "code": 200,
  "message": "Success",
  "data": {},
  "meta": {},
  "errors": null,
  "trace_id": "uuid"
}
```

Available canonical helpers:

| Method | HTTP | Purpose |
|--------|------|---------|
| `success()` | 200 | Generic success response |
| `created()` | 201 | Resource created |
| `accepted()` | 202 | Accepted or async work |
| `noContent()` | 204 | Empty success response |
| `badRequest()` | 400 | Invalid request shape |
| `unauthorized()` | 401 | Authentication required |
| `forbidden()` | 403 | Access denied |
| `notFound()` | 404 | Missing resource |
| `conflict()` | 409 | State conflict |
| `gone()` | 410 | Removed or deprecated resource |
| `unprocessable()` | 422 | Validation failure |
| `tooManyRequests()` | 429 | Rate-limited request |
| `internalError()` | 500 | Unexpected server error |

`warnings` may be included on success responses for partial success or deprecation messaging.

If you need a different top-level contract entirely, configure `response_formatter` with a class that implements `JOOservices\LaravelController\Contracts\ResponseFormatter`. That formatter receives the normalized response context and returns the final payload array.