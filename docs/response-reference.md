# Response reference

This document describes the exact shape of API responses for all response types. Keys are configurable via `config/laravel-controller.keys`; the table uses the default key names.

## Top-level keys

| Key       | Type    | Present in | Description |
|----------|---------|------------|-------------|
| `success` | boolean | All (when envelope used) | Whether the request is considered successful. By default any 2xx is success; configurable via `success_codes`. |
| `code`    | integer | All (when envelope used) | HTTP status code. |
| `message` | string | All (when envelope used) | Human-readable message. |
| `data`    | mixed  | Success responses, 204 with `envelope_204` (then `null`) | Response payload (object, array, or null). |
| `meta`    | object | When provided (e.g. pagination, links) | Extra metadata (e.g. `pagination`, `links`, `cursor`, `next_cursor`, `has_more`). |
| `errors`  | object \| null | Error responses | Validation field→messages or extra info (e.g. `retry_after` for 429). |
| `trace_id` | string | All (when envelope used) | Request correlation ID (from `X-Trace-ID` header or generated). |
| `warnings` | array | Success, when non-empty | Non-fatal warnings (e.g. deprecation, partial success). |

## 204 No Content

- **Without envelope** (`envelope_204` = false, default): body is `[]`, no envelope.
- **With envelope** (`envelope_204` = true): same envelope as other responses with `data: null`, `code: 204`, `message` and `trace_id` set.

## Success (2xx) example

```json
{
  "success": true,
  "code": 200,
  "message": "Success",
  "data": { "id": 1, "name": "Item" },
  "meta": {},
  "errors": null,
  "trace_id": "550e8400-e29b-41d4-a716-446655440000"
}
```

With pagination (`respondWithPagination`), `data` is the item array and `meta` includes e.g.:

- `pagination`: `{ "current_page", "total", "per_page", "last_page" }`
- `links` (if `pagination_links`): `{ "first", "last", "prev", "next" }`

With cursor (`respondWithCursorPagination`), `meta` includes: `cursor`, `next_cursor`, `has_more`.

## Validation error (422) example

```json
{
  "success": false,
  "code": 422,
  "message": "Unprocessable Entity",
  "data": null,
  "meta": {},
  "errors": {
    "email": ["The email field is required."],
    "name": ["The name must be at least 2 characters."]
  },
  "trace_id": "550e8400-e29b-41d4-a716-446655440000"
}
```

`errors` is always an object: keys are field names, values are arrays of message strings. The top-level `message` can be configured via `validation.message` (fixed string or `"first"` to use the first validation message).

## Rate limit (429) example

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

The response also includes a `Retry-After` header. Use `respondTooManyRequestsFromRequest()` to derive `retry_after` from the request (e.g. after throttle middleware).

## Server error (500) example

```json
{
  "success": false,
  "code": 500,
  "message": "Internal Server Error",
  "data": null,
  "meta": {},
  "errors": null,
  "trace_id": "550e8400-e29b-41d4-a716-446655440000"
}
```

When `app.debug` is true, `message` may be the exception message.

## Success range

By default, `success` is `true` for any 2xx. To treat only specific codes as success (e.g. 200 and 201, but not 202), set `success_codes` in config:

```php
'success_codes' => [200, 201],
```

Then a 202 response will still have `code: 202` but `success: false` if you want clients to distinguish “accepted / async” from “done”.
