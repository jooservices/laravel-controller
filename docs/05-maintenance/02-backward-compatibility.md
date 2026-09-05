# Backward Compatibility

## v4 breaking removals

- `paginated()` — use `respondWithPagination()`
- Flat cursor/offset fields on `meta` — use `meta.pagination.*`
- `envelope_204` — `noContent()` is always empty body

See [UPGRADE-4.0.md](../../UPGRADE-4.0.md).

## Still available (prefer newer aliases where noted)

- `success()` / prefer `respondWithData()`
- `error()` / prefer `respondWithError()`
- `created()`, `accepted()`, `conflict()`, `gone()`, …
- `noContent()` / prefer `respondNoContent()`
- `respondWithItem()`, `respondWithCollection()`
- `respondWithResource()`, `respondWithResourceCollection()`
- `respondWithPagination()`, `respondWithCursorPagination()`, `respondWithOffsetPagination()`
- `respondWithProblem()` (v4)

## Compatible additions (v4.0.1)

- `respondWithPagination()` accepts simple `Paginator` and `CursorPaginator` (in addition to LengthAware)
- Unrecognized paginator inputs throw instead of leaking raw payloads
- `JsonResource::additional()` / `with()` metadata is preserved in `meta`
