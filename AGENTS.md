# JOOservices Laravel Controller Repository Instructions

This repository is a Laravel package named `jooservices/laravel-controller`.

## Core intent

- Preserve the package's API-controller-first architecture.
- Favor minimal changes that fit the existing modules under `src/`.
- Treat tests, docs, and CI updates as part of the implementation.
- Read `README.md` and the structured docs before non-trivial coding.
- Follow `Request -> Controller -> FormRequest -> Service -> Repository -> Model`.
- Controllers must not contain business logic or direct persistence examples.
- Laravel Resource remains the presentation transformer; the response envelope only wraps Resource output.
- DTO, `Arrayable`, `JsonSerializable`, and `toArray()` input may be normalized, but DTO does not replace Resource.

## Repository quality rules

- Formatting authority: `Pint`
- Secondary style check: `PHP-CS-Fixer`, configured not to fight Pint
- Structural checks: `PHPCS`
- Static analysis: `PHPStan` with strict rules
- Maintainability checks: `PHPMD`
- Tests: `PHPUnit`

## Required command map

- `composer lint`
- `composer lint:all`
- `composer lint:fix`
- `composer test`
- `composer test:coverage`
- `composer check`
- `composer ci`

Do not invent alternate command names.

## Agent-first guidance

Before making non-trivial changes, also read:

- `.github/skills/repo-quality-foundation/SKILL.md`
- `.github/skills/code-style-and-conventions/SKILL.md`
- `.github/skills/architecture-and-design-principles/SKILL.md`
- `.github/skills/docs-and-readme-sync/SKILL.md`
- `.github/skills/ci-and-release/SKILL.md`

## Documentation policy

- Use the canonical product name `JOOservices Laravel Controller`.
- Use `jooservices/laravel-controller` only for the Composer package identifier.
- When public response behavior changes, update docs and examples in the same change.
- Prefer the structured documentation tree under `docs/` over adding new flat markdown files.
- README examples must use FormRequest and service-oriented application flow, not direct model queries.

## Git flow

- `develop` is the active integration branch.
- No `master` or `main` branch exists in the current remote state.
- Create feature branches from latest `develop`.
- Run `composer check` before commit.
- Commit author for agent work: `Viet Vu <jooservices@gmail.com>`.

## Change checklist

1. Keep the change minimal and module-appropriate.
2. Add or update tests when behavior changes.
3. Run the relevant lint and test commands.
4. Re-check docs, examples, CI assumptions, and release impact.
