# Response Reference

This JOOservices Laravel Controller reference describes the maintained response contract. Keys are configurable, but examples use the default names.

## Top-level keys

| Key | Type | Description |
|-----|------|-------------|
| `success` | boolean | Whether the response is considered successful |
| `code` | integer | HTTP status code |
| `message` | string | Human-readable summary |
| `data` | mixed | Response payload or `null` |
| `meta` | object | Extra metadata such as pagination or links |
| `errors` | object or `null` | Validation details or extra error information |
| `trace_id` | string | Request correlation identifier |
| `warnings` | array | Optional non-fatal warnings |

## 200 success example

```json
{
  "success": true,
  "code": 200,
  "message": "Success",
  "data": { "id": 1 },
  "meta": {},
  "errors": null,
  "trace_id": "550e8400-e29b-41d4-a716-446655440000"
}
```

## 422 validation error example

```json
{
  "success": false,
  "code": 422,
  "message": "Unprocessable Entity",
  "data": null,
  "meta": {},
  "errors": {
    "email": ["The email field is required."]
  },
  "trace_id": "550e8400-e29b-41d4-a716-446655440000"
}
```

## 429 rate limit example

```json
{
  "success": false,
  "code": 429,
  "message": "Too Many Requests",
  "data": null,
  "meta": {},
  "errors": { "retry_after": 60 },
  "trace_id": "550e8400-e29b-41d4-a716-446655440000"
}
```

`respondTooManyRequestsFromRequest()` can derive the retry value from the request context when your middleware sets it.