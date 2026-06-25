# Release Process

The repository's release automation is defined in `.github/workflows/release.yml`.

## Branch policy

- normal work starts from latest `develop` and targets `develop`
- release work starts from latest `develop` on `release/<version>`
- release pull requests target `master`
- after the release is merged into `master`, back-merge `master` into `develop` through a normal pull request

## Trigger

Push a semantic version tag matching:

```text
v*.*.*
```

Example:

```bash
git tag v1.3.1
git push origin v1.3.1
```

## Workflow stages

### 1. Create GitHub release

The workflow publishes a GitHub release with generated release notes for the pushed tag.

### 2. Refresh Packagist

When `PACKAGIST_USERNAME` and `PACKAGIST_TOKEN` are configured, the workflow refreshes the `jooservices/laravel-controller` package on Packagist.

## Practical maintainer checklist

Before tagging:

- confirm the intended release content has already merged to `master` through a reviewed release pull request
- confirm `master` and `develop` are synchronized according to the approved Git flow
- confirm `composer lint:all`, `composer test`, and `composer check` pass locally
- update release-facing docs, changelog entries, and examples when behavior or workflow changed

The current workflow is tag-driven and does not itself enforce which branch produced the tag. If the release source commit is unclear, stop and verify before tagging.
