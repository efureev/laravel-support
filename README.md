# PHP Laravel Support

![](https://img.shields.io/badge/php->=8.4-blue.svg)
![](https://img.shields.io/badge/Laravel->=13.0-red.svg)
[![PHP Laravel Package](https://github.com/efureev/laravel-support/actions/workflows/php.yml/badge.svg)](https://github.com/efureev/laravel-support/actions/workflows/php.yml)
[![Latest Stable Version](https://poser.pugx.org/efureev/laravel-support/v/stable?format=flat)](https://packagist.org/packages/efureev/laravel-support)
[![Total Downloads](https://poser.pugx.org/efureev/laravel-support/downloads)](https://packagist.org/packages/efureev/laravel-support)
[![License](https://poser.pugx.org/efureev/laravel-support/license)](https://packagist.org/packages/efureev/laravel-support)

A collection of helpers, traits, validation rules and service-provider utilities for modern Laravel applications.

## Requirements

* PHP `>= 8.4`
* Laravel `>= 13.0`

## Install

```bash
composer require efureev/laravel-support
```

The package is auto-discovered, no manual provider registration is required.

## Features

The full documentation lives in [`/docs`](docs/index.md). Below is a short overview.

### Validation rules — `Php\Support\Laravel\Rules`

* `Delimited` — validates a delimited string (e.g. comma-separated emails), with `min`/`max`, custom separator,
  duplicate control and trimming options.
* `Authorized` — validates that the current user is authorized (`can`) to use a given model by its key.
* `HasValidate` — helper trait that adds reusable `validate`/`validateValue` helpers.

### Eloquent traits — `Php\Support\Laravel\Traits`

* `Traits\Models\PostgresArray` — query scopes for searching inside native PostgreSQL arrays
  (`wherePgArrayContains`, `wherePgArrayContainsAny`, `wherePgArrayContainsOnly`, `wherePgArrayOverlapWith`).
  See `\Php\Support\Laravel\Tests\TestClasses\Models\PgArrayModel::scopeByTag`.
* `Traits\Models\HasModelEntityCache` — cache layer for model entities (with pluggable cachers).
* `Traits\Models\AllowToExecute` — guard helpers for model actions.
* `Traits\Models\WrapQuery` — query-wrapping helpers.
* `Traits\Modelable` / `Traits\ModelQueryable` — bind an Eloquent model into a request/class.
* `Traits\Requests\RequestModelable` — model resolution from requests.
* `Traits\Resources\HasMergeAdditional` — merge additional data into API resources.

### Sorting — `Php\Support\Laravel\Sorting`

* `Sorting\Model\Sortable` — model trait to make records sortable (works with drag'n'drop).
* `Sorting\Database\Sortable` — migration helpers (`columnSortingPosition`).

See [docs/sortable.md](docs/sortable.md).

### Service providers — `Php\Support\Laravel\ServiceProviders`

`AbstractServiceProvider` aggregates a set of helper traits: `HasCommands`, `HasPolicies`,
`HasPathHelpers`, `HasRegisters`, `HasBooting`. See [docs/sp.md](docs/sp.md).

### Pagination & Repositories

* `Pagination\PaginatedResourceArray` — nested paginated resource collections. See [docs/pagination.md](docs/pagination.md).
* `Repositories\AbstractRepository` — base Eloquent repository. See [docs/repository.md](docs/repository.md).

### Global helpers — `src/Global/base.php`

Autoloaded functions: `toCollect()`, `objectToArray()`. See [docs/global.md](docs/global.md).

## Test

### Local

```bash
composer test       # PHPCS + PHPUnit
composer test-cover # with coverage
```

### Docker

Runs the full test gate (PHPCS + PHPUnit) against PostgreSQL 18 inside containers.
No local PostgreSQL installation is required.

```bash
composer test:docker
# or
docker compose up --build --abort-on-container-exit --exit-code-from app
```

## Development

This is a library, so `composer.lock` is intentionally **not** committed and `"lock": false` is set in
`composer.json`. Every CI run (and local `composer update`) resolves the latest matching dependency versions,
which surfaces incompatibilities with new Laravel/PHP releases early. Pin versions in the consuming application,
not here.

## Changelog

See [CHANGELOG.md](CHANGELOG.md).

## License

The MIT License (MIT). See [LICENSE](LICENSE).
