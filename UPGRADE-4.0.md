# Upgrade to v4

v4 is a **breaking** release for response contracts and helpers. Pin `jooservices/laravel-controller:^4.0` and migrate before deploying.

## Breaking changes

1. **`paginated()` removed** — use `respondWithPagination()` (or cursor/offset helpers).
2. **Cursor and offset pagination meta nested under `meta.pagination`** — previously cursor/offset fields lived at the top level of `meta`. Length-aware pagination already used `meta.pagination`; cursor/offset now match.
3. **Iterable cursor helper no longer takes `$hasMore`** — `has_more` is `true` when `next_cursor` is non-null (CursorPaginator still uses `hasMorePages()`).
4. **`envelope_204` removed** (already on develop) — `noContent()` always returns an empty 204 body.
5. **Invalid API Resource classes throw** — no silent fallback to raw models.
6. **Status readiness** — any failed configured check returns HTTP **503** with `status: unavailable`.

## New in v4

| Feature | How to use |
| --- | --- |
| RFC 7807 Problem Details | `response_profile => 'problem+json'` or `$this->respondWithProblem(...)` |
| OpenAPI envelope schemas | `resources/openapi/envelope.v4.yaml` + `EnvelopeContract` constants |
| Meta header echo | `meta_headers` config (Idempotency-Key, rate-limit, Retry-After) — echo only |
| Pluggable health checks | `StatusHealthCheck` class-strings in `status.checks` |

## Migration snippets

### Replace `paginated()`

```php
// before
return $this->paginated($paginator);

// after
return $this->respondWithPagination($paginator, UserResource::class);
```

### Cursor / offset meta

```json
{
  "meta": {
    "pagination": {
      "cursor": "...",
      "next_cursor": "...",
      "has_more": true
    }
  }
}
```

Update clients that read `meta.cursor` / `meta.offset` to use `meta.pagination.*`.

### Optional Problem Details

```php
// config/laravel-controller.php
'response_profile' => 'problem+json', // errors only; success stays envelope
```

Or per call:

```php
return $this->respondWithProblem(
    title: 'Validation failed',
    status: 422,
    detail: 'One or more fields are invalid.',
    errors: $validator->errors()->toArray(),
);
```

### Custom status check

```php
use JOOservices\LaravelController\Contracts\StatusHealthCheck;

final class RedisCheck implements StatusHealthCheck
{
    public function name(): string
    {
        return 'redis';
    }

    public function check(): array
    {
        // probe...
        return ['ok' => true];
    }
}

// config
'status' => [
    'checks' => ['database', RedisCheck::class],
],
```

## Docs

- [CHANGELOG](CHANGELOG.md)
- [Problem Details](docs/02-user-guide/problem-details.md)
- [OpenAPI contract](docs/02-user-guide/openapi-contract.md)
- [Pagination and status](docs/02-user-guide/pagination-and-status.md)
- [Backward compatibility](docs/05-maintenance/02-backward-compatibility.md)
