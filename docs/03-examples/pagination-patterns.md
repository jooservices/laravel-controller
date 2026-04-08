# Pagination Patterns

Cursor-based pagination:

```php
$items = User::where('id', '>', $cursor)->orderBy('id')->limit(20)->get();
$nextCursor = $items->isNotEmpty() ? $items->last()->id : null;

return $this->respondWithCursorPagination(
    $items,
    $cursor,
    $nextCursor,
    $items->count() === 20,
    UserResource::class,
);
```

Offset-based pagination:

```php
$offset = (int) $request->get('offset', 0);
$limit = min(50, (int) $request->get('limit', 20));
$items = User::offset($offset)->limit($limit + 1)->get();
$hasMore = $items->count() > $limit;

if ($hasMore) {
    $items = $items->take($limit);
}

return $this->respondWithOffsetPagination($items, $offset, $limit, User::count(), UserResource::class);
```