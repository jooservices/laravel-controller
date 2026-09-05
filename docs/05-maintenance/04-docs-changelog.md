# Docs Changelog

Update documentation in the same change as behavior.

Required sync points:

- README quick start and version badge
- `CHANGELOG.md` and major `UPGRADE-*.md` when breaking
- `docs/README.md`
- architecture and user-guide pages (including Problem Details / OpenAPI)
- examples and configuration recipes
- development command docs
- AGENTS and AI skill guidance
- OpenAPI artifact `info.version` when the public contract changes

Use the canonical product name **JOOservices Laravel Controller** in prose. Use `jooservices/laravel-controller` only for the Composer package identifier.

## 4.0.0

- Added Problem Details, OpenAPI contract, meta headers, and pluggable health-check docs.
- Documented `meta.pagination` nesting and `paginated()` removal.
