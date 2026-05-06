# Copilot Instructions For `jooservices/laravel-controller`

Read [AGENTS.md](../AGENTS.md) as the primary repository policy.

When generating or editing code:

- prefer the existing package architecture over new abstractions
- match repository-native style and naming, not just formatter output
- keep tests and docs in the same change when public behavior moves
- prefer the structured `docs/` tree for documentation additions
- keep examples on FormRequest + service/repository architecture, not direct model queries
- keep Laravel Resources as presentation transformers; response envelopes only wrap output
- assume local and CI checks will enforce Pint, PHPCS, PHPStan, PHPMD, PHP-CS-Fixer, coverage, and tests
