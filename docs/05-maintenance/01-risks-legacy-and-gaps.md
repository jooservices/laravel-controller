# Risks, Legacy, And Gaps

- PHPStan 2 is enabled and should remain part of `composer lint:all`.
- The package supports simple route-file discovery, not a complete API versioning framework.
- `ResponseFormatter` customizes the envelope only; it should not absorb Resource or business logic responsibilities.
- The status endpoint can run built-in and custom checks. Keep checks disabled or minimal in latency-sensitive environments.
- `meta_headers` only echoes request headers; it is not an idempotency store or rate limiter.
- v4 removed `paginated()` and nested cursor/offset meta under `meta.pagination` — see [UPGRADE-4.0.md](../../UPGRADE-4.0.md).
- Prefer resource-friendly helper names (`respondWith*`) in new examples.
