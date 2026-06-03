# AGENTS.md

Guidance for AI agents working in this repository.

## Project overview

**php-js** (`alexbusu/php-js`) is a PHP Composer library that emits JSON "triggers" from PHP for a jQuery client script (`src/js/jquery.setup.js`) to execute in the browser. There is no runnable application in this repo; development means running unit tests and static analysis on the library source.

## Cursor Cloud specific instructions

### System requirements

PHP **8.1+** is required (`composer.json`). The bundled **Psalm phar** (v6+) needs PHP **8.3.16+** (or matching 8.1/8.2/8.4 patch levels). On Ubuntu 24.04, use the [ondrej/php PPA](https://launchpad.net/~ondrej/+archive/ubuntu/php) if the default `php-cli` is too old for Psalm.

Required PHP extensions:

- `ext-json` (package `php-json` or bundled in `php-cli`)
- `ext-dom` (package `php-xml`)

Composer 2.x is required.

Docker is **optional**. The `Makefile` wraps Composer/PHPUnit/Psalm/Rector in Docker images (`composer:2.6.5`, `php:8.3.0-cli-alpine`), but local PHP + Composer works without Docker.

No environment variables or secrets are needed.

### Dependency install

From the repo root (handled automatically on VM startup via the update script):

```bash
composer install --no-interaction
```

`composer.lock` is gitignored; `composer install` resolves dev dependencies fresh each time.

### Lint, test, and static analysis

| Task | Command |
|------|---------|
| Unit tests | `vendor/bin/phpunit` |
| Static analysis | `vendor/bin/psalm.phar --show-info=true` |
| Refactor check | `vendor/bin/rector --dry-run` |

Makefile equivalents (require Docker): `make phpunit`, `make psalm`, `make rector-dry-run`.

CI (`.github/workflows/phpunit.yml`) runs PHPUnit, Psalm, and Rector on PHP 8.1–8.4 with both lowest and highest dependency sets.

### Running / demonstrating the library

There is no dev server or demo app in the repo. To exercise the core API locally, use a one-off PHP script with `vendor/autoload.php` and `Alexbusu\Phpjs::response()`, then `json_encode()` the result. Example triggers: `message()`, `redirect()`, `consoleLog()`, `trigger()`.

Browser-side behavior requires jQuery plus `src/js/jquery.setup.js` in a consuming application; that is outside this repository.

### Gotchas

- **Rector `--dry-run` exits 2** when it would apply changes. That means suggested refactors exist, not that the environment is broken.
- **PHPUnit** may report deprecation notices; tests can still pass.
- **Psalm phar** performs a PHP version check before running; upgrade PHP if you see "Your system is not ready to run the application."
