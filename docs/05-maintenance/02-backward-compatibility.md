# Backward Compatibility

Backward-compatible helpers remain available:

- `success()`
- `error()`
- `created()`
- `noContent()`
- `respondWithItem()`
- `respondWithCollection()`
- `paginated()`

New code should prefer:

- `respondWithData()`
- `respondWithResource()`
- `respondWithResourceCollection()`
- `respondWithPagination()`
- `respondWithError()`
- `respondNoContent()`

Do not change the default envelope keys, status codes, or trace ID behavior without tests and documentation.
