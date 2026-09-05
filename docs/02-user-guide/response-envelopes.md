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

`warnings` is included only when warnings are present.

## Resource-Friendly Helpers

- `respondWithData()`
- `respondWithResource()`
- `respondWithResourceCollection()`
- `respondWithPagination()` / `respondWithCursorPagination()` / `respondWithOffsetPagination()`
- `respondWithError()` / `respondWithProblem()` (RFC 7807)
- `respondNoContent()`

Legacy aliases such as `success()`, `error()`, `respondWithItem()`, and `respondWithCollection()` remain available. `paginated()` was removed in v4 — use `respondWithPagination()`.

See [Problem Details](./problem-details.md) and [OpenAPI Contract](./openapi-contract.md).

## Resource Boundary

Use Laravel Resources for presentation. The package envelope wraps Resource output; it does not replace Resource classes.

DTOs, `Arrayable`, `JsonSerializable`, and objects with `toArray()` are normalized as accepted response input data only.
