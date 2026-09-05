# Pagination and Status

## Pagination helpers

Use these helpers when returning resource collections:

- `respondWithCollection($items, ResourceClass::class)`
- `respondWithPagination($paginator, ResourceClass::class)` — LengthAwarePaginator, simple `Paginator`, or `CursorPaginator`
- `respondWithCursorPagination($itemsOrCursorPaginator, …)` — iterable API or Laravel `CursorPaginator`
- `respondWithOffsetPagination($items, $offset, $limit, $total, ResourceClass::class)`

Unrecognized inputs to `respondWithPagination()` throw `UnexpectedValueException` (v4.0.1+); they are never returned as raw `data`.

All three pagination styles nest fields under **`meta.pagination`** (v4). When `pagination_links` is enabled, HAL-style navigation links are under `meta.links`.

### Length-aware example (`meta.pagination`)

```json
{
  "meta": {
    "pagination": {
      "current_page": 1,
      "total": 100,
      "per_page": 15,
      "last_page": 7
    },
    "links": {
      "first": "...",
      "last": "...",
      "prev": null,
      "next": "..."
    }
  }
}
```

### Cursor example

```php
return $this->respondWithCursorPagination(
    items: $users->cursorPaginate(15), // CursorPaginator
    resourceClass: UserResource::class,
);
```

Or supply an iterable plus cursors (`has_more` is derived from a non-null `next_cursor`):

```php
return $this->respondWithCursorPagination(
    items: $pageItems,
    cursor: $request->validated('cursor'),
    nextCursor: $next,
    resourceClass: UserResource::class,
);
```

## Status endpoint

The package can expose a status endpoint beneath the configured route prefix. Depending on config, the response may include:

- application version
- current environment
- maintenance mode state
- optional health checks

Built-in check names: `database`, `cache`, `queue`.

Custom checks: class-strings implementing `JOOservices\LaravelController\Contracts\StatusHealthCheck` (resolved via the container). Mixed lists are supported:

```php
'status' => [
    'checks' => ['database', 'cache', App\Health\RedisCheck::class],
    'checks_timeout_seconds' => 5,
],
```

Use `status.checks_timeout_seconds` to limit how long health checks may run. Any failed check returns HTTP **503** and `status: unavailable`.
