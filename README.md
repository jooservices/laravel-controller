# JOOservices Laravel Controller

[![codecov](https://codecov.io/gh/jooservices/laravel-controller/branch/develop/graph/badge.svg)](https://codecov.io/gh/jooservices/laravel-controller)
[![CI](https://github.com/jooservices/laravel-controller/actions/workflows/ci.yml/badge.svg?branch=develop)](https://github.com/jooservices/laravel-controller/actions/workflows/ci.yml)
[![OpenSSF Scorecard](https://api.securityscorecards.dev/projects/github.com/jooservices/laravel-controller/badge)](https://securityscorecards.dev/viewer/?uri=github.com/jooservices/laravel-controller)
[![PHP Version](https://img.shields.io/badge/PHP-8.5%2B-blue.svg)](https://www.php.net/)
[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](LICENSE)
[![Packagist Version](https://img.shields.io/packagist/v/jooservices/laravel-controller)](https://packagist.org/packages/jooservices/laravel-controller)

The **JOOservices Laravel Controller** package provides a standardized base controller for Laravel APIs with consistent envelopes, pagination helpers, status endpoints, and exception-to-response mapping.

Package name: `jooservices/laravel-controller`

## Install

```bash
composer require jooservices/laravel-controller
```

## Quick example

```php
namespace App\Http\Controllers\Api\V1;

use App\Http\Resources\UserResource;
use App\Models\User;
use JOOservices\LaravelController\Http\Controllers\BaseApiController;

class UserController extends BaseApiController
{
    public function index()
    {
        return $this->respondWithPagination(User::paginate(), UserResource::class);
    }

    public function show(User $user)
    {
        return $this->respondWithItem($user, UserResource::class);
    }
}
```

## What the package supports today

- standardized JSON response envelopes for success and error states
- optional custom response formatter support for teams that need a different top-level contract
- item, collection, length-aware pagination, cursor pagination, and offset pagination helpers
- request trace correlation through `X-Trace-ID`
- optional HAL-style pagination and item links
- configurable `204` envelope behavior, validation message strategy, and success-code range
- optional status endpoint with environment, maintenance, version, and health-check metadata
- exception handling helpers designed for Laravel API controllers

## Documentation

Start with:

- [Documentation Hub](docs/README.md)
- [Installation](docs/01-getting-started/installation.md)
- [Quick Start](docs/01-getting-started/quick-start.md)
- [Response Envelopes](docs/02-user-guide/response-envelopes.md)
- [Pagination and Status](docs/02-user-guide/pagination-and-status.md)
- [Response Reference](docs/02-user-guide/response-reference.md)

## AI Support

This repository now includes a lightweight AI skill pack aligned with the `jooservices/dto` repository structure.

Start with:

- [AGENTS.md](AGENTS.md)
- [CLAUDE.md](CLAUDE.md)
- [AI Skills Map](ai/skills/README.md)
- [AI Skills Usage Guide](ai/skills/USAGE.md)

The canonical repository skills live under `.github/skills/`, with adapter files for GitHub Copilot and other agent runtimes.

## Development

```bash
composer lint:all
composer test
```

Contributor workflow details live in:

- [Setup](docs/04-development/setup.md)
- [Coding Standards](docs/04-development/coding-standards.md)
- [Testing](docs/04-development/testing.md)
- [CI/CD](docs/04-development/ci-cd.md)
- [AI Skills](docs/04-development/ai-skills.md)

## GitHub Actions and Services

Composer command parity with `jooservices/dto`:

- core command map matches: `test`, `test:coverage`, `lint:pint`, `lint:pint:fix`, `lint:phpcs`, `lint:phpstan`, `lint:phpmd`, `lint`, `lint:all`, `lint:fix`, `check`, and `ci`
- intentional differences remain: this package does not include `lint:cs` or `lint:cs:fix` because `php-cs-fixer` is not part of this repository toolchain

Local git hook consistency:

- `captainhook` is installed through Composer hooks
- `commit-msg`, `pre-commit`, and `pre-push` checks are defined in `captainhook.json`
- `gitleaks` is part of the local hook policy, matching the `dto` repository conventions

Current GitHub Actions coverage:

- `CI`: Composer audit, lint matrix, tests, coverage artifacts, optional Codecov upload
- `Release`: tag-driven GitHub release with optional Packagist refresh
- `PR Labeler`: applies labels from changed-file rules in `.github/labeler.yml`
- `Semantic PR Title`: enforces conventional pull request titles
- `OpenSSF Scorecard`: publishes repository security posture results as SARIF
- `Secret Scanning`: workflow exists, but the `gitleaks` job is disabled until `GITLEAKS_LICENSE` is configured

External services currently supported by workflows:

- `Codecov` in `.github/workflows/ci.yml` when `CODECOV_TOKEN` is configured
- `Packagist` refresh in `.github/workflows/release.yml` when `PACKAGIST_USERNAME` and `PACKAGIST_TOKEN` are configured
- `OpenSSF Scorecard` in `.github/workflows/scorecard.yml`
- `GitHub SARIF` upload in `.github/workflows/scorecard.yml`

## License

This package is distributed under the MIT license.
