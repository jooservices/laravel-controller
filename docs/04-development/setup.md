# Setup

## Prerequisites

- PHP `>=8.5`
- Composer 2.x
- Git
- `gitleaks` for the default local git hook flow

Install dependencies:

```bash
composer install
```

`composer install` also installs the local git hooks via `captainhook`.

## Install gitleaks

The repository's `pre-commit` and `pre-push` hooks run `gitleaks`. Install it before relying on the default local workflow.

On macOS:

```bash
brew install gitleaks
```

Verify the installation:

```bash
gitleaks version
```

If hooks were installed before `gitleaks` was available, reinstall them after setup:

```bash
vendor/bin/captainhook install --force --skip-existing
```

The relevant hook commands are:

```bash
gitleaks protect --staged --verbose --redact --config=.gitleaks.toml
gitleaks detect --verbose --redact --config=.gitleaks.toml --log-opts="origin/develop..HEAD"
```

Useful local commands:

```bash
composer lint:all
composer lint:fix
composer test
composer test:coverage
composer check
composer ci
```
