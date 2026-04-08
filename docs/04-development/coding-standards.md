# Coding Standards

Repository quality tools:

- `Pint` for formatting
- `PHPCS` for structural coding-standard checks
- `PHPStan` for static analysis
- `PHPMD` for maintainability checks
- `PHPUnit` for behavior verification

Implementation rules:

- keep behavior in the existing module that already owns it
- preserve public response contracts unless the change explicitly updates them
- update docs and tests together when public behavior changes
- prefer small trait-level or provider-level changes over introducing new abstractions