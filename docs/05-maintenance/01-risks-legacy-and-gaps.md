# Risks, Legacy, And Gaps

- PHPStan 2 is enabled and should remain part of `composer lint:all`.
- The package supports simple route-file discovery, not a complete API versioning framework.
- `ResponseFormatter` customizes the envelope only; it should not absorb Resource or business logic responsibilities.
- The status endpoint can run database, cache, and queue checks. Keep checks disabled or minimal in latency-sensitive environments.
- Legacy helper names remain for compatibility. Prefer the resource-friendly names in new examples.
