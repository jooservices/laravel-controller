# Pagination and Status

## Pagination helpers

Use these helpers when returning resource collections:

- `respondWithCollection($items, ResourceClass::class)`
- `respondWithPagination($paginator, ResourceClass::class)`
- `respondWithCursorPagination($items, $cursor, $nextCursor, $hasMore, ResourceClass::class)`
- `respondWithOffsetPagination($items, $offset, $limit, $total, ResourceClass::class)`

When `pagination_links` is enabled, the package adds HAL-style navigation links under `meta.links`.

## Status endpoint

The package can expose a status endpoint beneath the configured route prefix. Depending on config, the response may include:

- application version
- current environment
- maintenance mode state
- optional health checks for `database`, `cache`, and `queue`

Use `status.checks_timeout_seconds` to limit how long health checks may run.