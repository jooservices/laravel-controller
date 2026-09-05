# Repository Structure

The important package areas are:

```text
src/
  config/laravel-controller.php
  Contracts/
  Formatters/
  OpenApi/
  Support/
  Http/Controllers/
  Providers/
  Traits/
routes/api/v1.php
resources/lang/en/
resources/openapi/envelope.v4.yaml
tests/Unit/
tests/Feature/
docs/
UPGRADE-4.0.md
```

Ownership guidelines:

- `src/Traits/HasApiResponses.php`: response envelope assembly and response helper behavior
- `src/Formatters/ProblemDetailsFormatter.php`: RFC 7807 error shaping
- `src/OpenApi/EnvelopeContract.php` + `resources/openapi/`: stable schema names / OpenAPI artifact
- `src/Contracts/StatusHealthCheck.php` + `src/Support/StatusHealthChecker.php`: readiness probes
- `src/Traits/HandlesApiExceptions.php`: exception-to-response mapping
- `src/Http/Controllers/`: package entry controllers such as status endpoints
- `src/Providers/`: configuration, route registration, publishing, and package bootstrapping
- `src/config/`: package configuration defaults