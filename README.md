# PHP Laravel Support

![](https://img.shields.io/badge/php->=8.5-blue.svg)
![](https://img.shields.io/badge/Laravel->=13.0-red.svg)
[![PHP Laravel Package](https://github.com/efureev/laravel-support/actions/workflows/php.yml/badge.svg)](https://github.com/efureev/laravel-support/actions/workflows/php.yml)
[![Latest Stable Version](https://poser.pugx.org/efureev/laravel-support/v/stable?format=flat)](https://packagist.org/packages/efureev/laravel-support)
[![Total Downloads](https://poser.pugx.org/efureev/laravel-support/downloads)](https://packagist.org/packages/efureev/laravel-support)
[![License](https://poser.pugx.org/efureev/laravel-support/license)](https://packagist.org/packages/efureev/laravel-support)

Validation rules, Eloquent traits and service-provider scaffolding for Laravel packages and
applications. No dependencies beyond the Illuminate components it actually uses.

## Requirements

* PHP `>= 8.5`
* Laravel `>= 13.0`

## Install

```bash
composer require efureev/laravel-support
```

Auto-discovered — no manual provider registration. To override the shipped messages:

```bash
php artisan vendor:publish --tag=laravel-support-lang
```

## What is in it

Full reference in [`docs/`](docs/index.md); the sharp edges are collected in
[docs/pitfalls.md](docs/pitfalls.md).

| | |
|---|---|
| [Validation rules](docs/rules.md) | `Delimited` validates each item of a delimited string; `Authorized` checks a gate against the model a key points at; `HasValidate` adds one-off validation and typed request reads |
| [PostgreSQL arrays](docs/postgres-array.md) | a cast and four query scopes for native `text[]` / `integer[]` columns, plus the literal encoder underneath |
| [Sortable](docs/sortable.md) | hand-ordered model stacks (drag-and-drop friendly), with a migration helper and optional global scopes |
| [Service providers](docs/service-providers.md) | `AbstractServiceProvider` with runtime-aware boot hooks and helpers for configs, routes, translations, views, policies, commands and bindings |
| [Model cache](docs/model-cache.md) | per-model caching with automatic invalidation and a pluggable cacher |
| [Model traits](docs/model-traits.md) | bind a model into a request or service, resolve it from input, forbid selected methods |
| [Resources & pagination](docs/resources.md) | nest a paginated resource collection inside another resource; make `additional()` merge |
| [Repository](docs/repository.md) | a thin Eloquent repository base |
| [Global helpers](docs/global.md) | `toCollect()`, `objectToArray()` |

## Upgrading

5.0 raises the PHP floor to 8.5, drops the `efureev/support` dependency and moves three exception
classes. See [docs/upgrade-5.0.md](docs/upgrade-5.0.md).

## Tests

The suite runs against a real PostgreSQL, and the Redis cacher against a real Redis — both bugs
those cover were invisible to anything less.

```bash
composer test         # PHPCS + PHPStan + PHPUnit
composer test-cover   # the same, with coverage
composer test:docker  # the full gate in containers, no local services needed
```

Redis tests skip themselves when no server is reachable; `composer test:docker` and CI both
provide one.

## Development

This is a library, so `composer.lock` is intentionally **not** committed and `"lock": false` is
set in `composer.json`. Every CI run resolves the latest matching dependency versions, which
surfaces incompatibilities with new Laravel/PHP releases early. Pin versions in the consuming
application, not here.

`.gitattributes` keeps tests, docs, CI and Docker out of the Composer archive — only `src/`,
`resources/`, `composer.json`, `README.md`, `CHANGELOG.md` and `LICENSE` reach a consumer's
`vendor/`. Verify with `git archive --format=tar HEAD | tar -t`.

## Changelog

See [CHANGELOG.md](CHANGELOG.md).

## License

The MIT License (MIT). See [LICENSE](LICENSE).
