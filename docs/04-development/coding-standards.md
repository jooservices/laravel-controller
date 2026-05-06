# Coding Standards

Repository quality tools:

- `Pint` for formatting
- `PHP-CS-Fixer` as a secondary non-conflicting style check
- `PHPCS` for structural coding-standard checks
- `PHPStan` with strict rules for static analysis
- `PHPMD` for maintainability checks
- `PHPUnit` for behavior verification

Implementation rules:

- keep behavior in the existing module that already owns it
- preserve public response contracts unless the change explicitly updates them
- update docs and tests together when public behavior changes
- prefer small trait-level or provider-level changes over introducing new abstractions
- keep Laravel Resources as the presentation transformer; response envelopes only wrap output
