# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## What this is

`hero-seguros/hero-laratoolkit` is a shared Composer library consumed by the ~40 Laravel microservices in the Hero Seguros workspace (see `/Users/mario/sandbox/hero/CLAUDE.md` for the broader workspace context). It is **not** a runnable application — it has no `vendor/`, no entry point, no test suite, and `config/` and `routes/` are intentionally empty placeholder directories.

Because every consumer service depends on this package, any breaking change here has fan-out across the entire fleet. Prefer additive changes; when modifying existing behavior, search consumers in sibling directories before changing public signatures.

## Install / build

There are no Composer scripts, no test runner, no linter, and no CI config in this repo. The only command relevant here is:

```bash
composer install        # only needed if you add/change a dependency
composer validate       # sanity-check composer.json
```

To test changes against a consumer service, point that service's `composer.json` at this directory via a path repository, then `composer update hero-seguros/hero-laratoolkit` in the consumer.

## Supported runtimes

- **PHP:** `>=8.0`
- **Laravel (illuminate/*):** `^8.0 | ^9.0 | ^10.0 | ^11.0 | ^12.0`

Note: the README still claims Laravel 8.x/9.x/10.x. The actual constraint in `composer.json` is wider (up to 12.x) — trust `composer.json`. When touching code that uses framework APIs, keep it compatible across the full range; do not adopt syntax/APIs that only exist in newer versions without guarding.

## Architecture

PSR-4 root: `HeroLaraToolkit\` → `src/`.

Auto-registration is handled by `HeroLaraToolkit\Providers\HeroLaraToolkitServiceProvider` via the `extra.laravel.providers` array in `composer.json` (Laravel package auto-discovery). The provider has two responsibilities that together make this package "just work" after `composer require`:

1. **Registers six custom `Validator::extend` rules** in `boot()`: `cpf`, `cnpj`, `phone`, `cellphone`, `cep`, `passport`. These become globally available in every consumer's `Validator`/Form Request rules — consumers should not redefine them. The implementations live in `src/Helpers/ValidatorHelper.php`.
2. **Registers two Artisan generator commands** (only when `runningInConsole()`): `make:service` and `make:repository`.

### Generator commands (`src/Commands/`)

Both extend Laravel's `GeneratorCommand` and use stubs in `src/Commands/stubs/`.

- `php artisan make:service {name} {domain}` → writes `app/Services/{Domain}/{Name}Service.php`. Note the **two positional args**: domain comes second, gets `ucfirst`'d, and becomes a subdirectory. Stub gives the class a single `execute(): void` method — this matches the workspace convention that "each service class has one public `execute()` method" (see workspace `CLAUDE.md`).
- `php artisan make:repository {name}` → writes `app/Repositories/{Name}Repository.php` extending `AbstractRepository` with `protected string $modelClass = App\Models\{Name}::class`. The `{name}` arg is interpreted as the Eloquent model name, not the repository name.

When editing these commands or stubs, remember the output paths and namespace conventions are load-bearing for consumers' autoloaders.

### `AbstractRepository` (`src/Abstractions/`)

Tiny base for Eloquent repositories. Public surface: `getEmptyModel()`, `getById(int)`, `insert(array)`, `save(Model)`, `update(Model, array)`, `getAll()`. Subclasses only set `protected string $modelClass`. Intentionally minimal — consumers add their own query methods.

### `ApiControllerTrait` (`src/Traits/`)

Standardizes JSON API responses for controllers across all services. Two methods:

- `returnSuccess($data, ?string $message, int $statusCode = 200)` — wraps payload as `{success: true, message, data}`.
- `returnError(string $message, ?Throwable $exception, int $statusCode = 400)` — wraps as `{success: false, message}` with three behaviors worth knowing:
  - If the exception is a `HeroLaraToolkit\Exceptions\BusinessException`, the exception's own message **replaces** the passed-in `$message`. This is the contract: throw `BusinessException` when you want the message surfaced to the API caller verbatim.
  - If the exception is an `Illuminate\Auth\Access\AuthorizationException`, the response is forced to 403 with a fixed Portuguese message.
  - Outside `production`, the response includes a `data` block with class/file/line/trace — do not change this without considering it leaks stack traces in dev/staging by design.
  - Logging is done by resolving `Psr\Log\LoggerInterface` from the container directly (not via the `Log` facade) so the trait works in contexts where facades aren't bootstrapped.

### Helpers (`src/Helpers/`)

- `ValidatorHelper` — boolean validators for Brazilian formats. Used both by the auto-registered validation rules and directly. `cellphone` requires the subscriber part to start with `9`; `phone` (landline) requires `2-5`. CPF/CNPJ implement the official check-digit algorithms.
- `FormatHelper` — pure-string formatters (CPF/CNPJ/CEP/phone masks, BR/MySQL date conversions, BR float).
- `DebugHelper::inFile($fileName, $data)` — appends data to a file under `App::basePath()`. Useful for ad-hoc debugging in consumer services; not for production use.

### `BusinessException` (`src/Exceptions/`)

Empty marker subclass of `\Exception`. Only purpose is the behavioral contract with `ApiControllerTrait::returnError` described above — throwing it is how you say "this message is safe to return to the API caller."

## Conventions when extending

- **Don't break the auto-registration contract.** Anything added to `HeroLaraToolkitServiceProvider::boot()` runs in every consumer service on every request. Validator rule names registered here become reserved across the fleet.
- **Keep helpers static and dependency-free** where possible — they get called from anywhere in consumer code and shouldn't require container wiring.
- **No tests yet.** If adding non-trivial logic (e.g., new validators, new helpers), consider adding a `phpunit/phpunit` dev dependency and a `tests/` directory; there is currently no precedent to follow in this repo.
- **README is in Portuguese and partially stale** (Laravel version range, missing helpers/validators). Update it alongside meaningful public-API changes.
