# Pagination Patterns

Use services to fetch paginated data, then pass the paginator and Resource class to the controller helper.

```php
public function index(UserIndexRequest $request, UserService $users): JsonResponse
{
    return $this->respondWithPagination(
        paginator: $users->paginate($request->validated()),
        resourceClass: UserResource::class,
        message: 'Users retrieved successfully.',
    );
}
```

Cursor-style and offset-style helpers are available for services that do not return Laravel length-aware paginators. Fields nest under `meta.pagination`.

Pass a Laravel `CursorPaginator` for automatic cursor / links extraction:

```php
return $this->respondWithCursorPagination(
    items: $users->cursorPaginate(15),
    resourceClass: UserResource::class,
);
```

Or supply an iterable plus cursors:

```php
return $this->respondWithCursorPagination(
    items: $users->cursorPage($request->validated()),
    cursor: $request->validated('cursor'),
    nextCursor: $users->nextCursor(),
    resourceClass: UserResource::class,
);
```

```php
return $this->respondWithOffsetPagination(
    items: $users->offsetPage($request->validated()),
    offset: $request->integer('offset'),
    limit: $request->integer('limit'),
    total: $users->countFor($request->validated()),
    resourceClass: UserResource::class,
);
```
