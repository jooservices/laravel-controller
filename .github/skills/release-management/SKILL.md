---
name: release-management
description: "Use when preparing, validating, tagging, or publishing jooservices/laravel-controller releases."
---

# Release Management Skill

## Repository Truth

- Package: `jooservices/laravel-controller`
- Versioning follows semantic versioning: `MAJOR.MINOR.PATCH`, tagged as `vX.Y.Z`.
- Normal changes branch from `develop` and open PRs to `develop`.
- Release branches are `release/<version>` from latest `develop` and open PRs to `master`.
- Never commit directly to `master` or `develop`; all updates to those branches must go through pull requests.
- Stop and ask if version scope, branch state, reviews, release metadata, or compatibility impact is unclear.

## Version Decision

- Patch: compatible fixes, documentation corrections, CI maintenance, dependency patch updates.
- Minor: backward-compatible response helpers, status endpoint features, configuration additions.
- Major: breaking response envelope changes, removed public APIs, changed pagination/status contracts, dropped PHP or Laravel support.

Do not widen Laravel/PHP constraints or drop supported versions without explicit approval.

## Preflight

1. Inspect tags and releases:
   - `git tag --sort=-version:refname`
   - `gh release list --repo jooservices/laravel-controller`
2. Inspect `CHANGELOG.md`, `README.md`, `composer.json`, `composer.lock`, and `.github/workflows/release.yml`.
3. Confirm branch state:
   - feature/fix PRs target `develop`
   - release PRs target `master`
   - hotfix PRs target `master` only when clearly identified
4. Validate locally:
   - `composer validate --strict`
   - `composer lint:all`
   - `composer test`

## Release Flow

1. Create `release/<version>` from latest `develop`.
2. Update changelog and release metadata on the release branch only.
3. Open release PR to `master`.
4. Merge only after required checks pass, required reviews are approved, no requested changes remain, no unresolved review threads remain, and the branch is mergeable.
5. Tag latest `master` with `vX.Y.Z`.
6. Create or verify GitHub release and package publication.
7. Merge `master` back into `develop` through a pull request and normal review/check gates.
8. Delete only safely merged release branches.

## Failure Rules

- Do not force push or bypass protected branch requirements.
- If checks, reviews, or review threads cannot be verified, stop and report.
