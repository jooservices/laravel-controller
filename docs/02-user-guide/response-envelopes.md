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
- `respondWithPagination()`
- `respondWithError()`
- `respondNoContent()`

Backward-compatible helpers such as `success()`, `error()`, `respondWithItem()`, `respondWithCollection()`, and `paginated()` remain available.

## Resource Boundary

Use Laravel Resources for presentation. The package envelope wraps Resource output; it does not replace Resource classes.

DTOs, `Arrayable`, `JsonSerializable`, and objects with `toArray()` are normalized as accepted response input data only.
