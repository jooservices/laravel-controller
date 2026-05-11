# Claude Code Instructions For `jooservices/laravel-controller`

Read [AGENTS.md](AGENTS.md) first.

When working in this repository:

- prefer the smallest change that fits the existing package structure
- match repository-native style and naming
- understand whether behavior belongs in controllers, traits, routes, config, or the service provider before editing
- keep tests and docs in the same change when public behavior moves
- keep examples on FormRequest + service/repository architecture, not direct model queries
- keep Laravel Resources as presentation transformers; response envelopes only wrap output
- assume CI will enforce Pint, PHPCS, PHPStan, PHPMD, PHP-CS-Fixer, coverage, and tests
- use `develop` as the active integration branch
- use `master` as the stable/release branch
