# Repository Structure

The important package areas are:

```text
src/
  config/laravel-controller.php
  Http/Controllers/
  Providers/
  Traits/
routes/api/v1.php
resources/lang/en/
tests/Unit/
tests/Feature/
docs/
```

Ownership guidelines:

- `src/Traits/HasApiResponses.php`: response envelope assembly and response helper behavior
- `src/Traits/HandlesApiExceptions.php`: exception-to-response mapping
- `src/Http/Controllers/`: package entry controllers such as status endpoints
- `src/Providers/`: configuration, route registration, publishing, and package bootstrapping
- `src/config/`: package configuration defaults