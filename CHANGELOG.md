<!--- BEGIN HEADER -->
# Changelog

All notable changes to this project will be documented in this file.
<!--- END HEADER -->

## [5.0.0](https://github.com/efureev/laravel-support/compare/v4.0.0...v5.0.0) (2026-09-13)

### ⚠ BREAKING CHANGES

* Require PHP >= 8.5
* Drop the `efureev/support` dependency. `InvalidParamException`, `UnknownMethodException` and
  `MethodNotAllowedException` now live under `Php\Support\Laravel\Exceptions\`. Each keeps its
  SPL parent, so `catch (\LogicException)`, `catch (\BadMethodCallException)` and
  `catch (\RuntimeException)` are unaffected — catches on the old FQCNs are not.
* Remove `Traits\Models\WrapQuery` (two lines, no users; `Builder::tap()` covers it)
* Remove `Test\CreateHttpRequests` (a verbatim copy of the framework's private test plumbing)
* Declare the Illuminate components the package actually uses instead of `illuminate/database`
  alone, plus `ext-json` and `ext-mbstring`

### Bug Fixes

* `Delimited`: substitute `:min` / `:max` in the shipped messages — the rule passed
  `minimum` / `maximum` while the language files expected `:min` / `:max`, so users saw the raw
  placeholder
* `Delimited`: stop counting whitespace-only items towards `min` / `max`
* `Delimited`: pluralise the message noun from the boundary rather than the actual count
* `Delimited`: reject an empty separator instead of failing inside `explode()`
* `PostgresArray` scopes: validate and quote the column name — it was interpolated into raw SQL
  unescaped, so a crafted name could close the expression
* `PostgresArray` scopes: reject a set where a single element is expected, instead of letting the
  driver fail with an unrelated message
* `RedisCacher::cacheForgetCollection()`: call `eval` through the connection rather than the raw
  client (phpredis and predis take opposite argument orders, so it raised a `TypeError` on the
  default client), keep the whole key table in Lua instead of binding only its first entry, and
  include the cache prefix in the `KEYS` pattern
* `HasModelEntityCache`: instantiate `cache.resolver.class` instead of calling it as a function,
  and reject a class that does not implement `CacherContract`
* `HasModelEntityCache::cacheForgetCollection()`: honour the disabled flag, like its per-entity
  counterpart already did
* `ServiceProvider`: publish translations to `$app->langPath()` — `resource_path('lang/…')` is
  not where Laravel 9+ reads them — under the `laravel-support-lang` tag
* `HasBooting::bootMethod()`: memoise per instance; a `static` local in an inherited method is
  shared with every subclass since PHP 8.1, so the first provider decided for all the others
* `AbstractRepository::findModel()`: accept integer keys, which the default Eloquent key type
  uses, and preserve the looked-up id on `ModelNotFoundException`
* `RequestModelable`: read the unqualified key name — a dot in `$request->input()` means nested
  access, so the table-qualified name never resolved anything
* `HasRegisters`: report a missing config or route file by name instead of passing `null` into
  the framework
* `SortOrderingAsc` / `SortOrderingDesc`: reject a model without the `Sortable` trait instead of
  ordering by a column that does not exist
* PostgreSQL array decoding: treat a single quote as an ordinary character. `array_out` quotes
  with double quotes only, so `decode("{safe,o'brien}")` used to swallow the second element and
  return `['safe']`. A hand-written `{'x','y'}` now decodes to `["'x'", "'y'"]`

### Features

* Add `Casts\PostgresArrayCast` — the cast that makes the PostgreSQL array scopes usable, until
  now only a test fixture
* Add `Helpers\PostgresArray` (`encode()`, `decode()`, `toIndexedArray()`), replacing the
  dropped dependency
* Add `HasModelEntityCache::enableCache()`, `withoutCache()`, `forget()` and
  `flushResolvedCacher()` — the missing halves of `disableCache()` and `remember()`
* Add `AllowToExecute::removeMethodFromAllowList()` and `removeMethodFromDisallowMap()`; make
  `isAllowToExecute()` public and the allow-list helpers chainable
* Add `Modelable::modelInputKeyName()` for sources that name the key differently from the column
* `HasPathHelpers::getDatabaseSeedersPath()` takes an optional argument, like its siblings
* Add `PostgresArray::pgArrayAppend()` and `pgArrayRemove()` — the write half of the four read
  scopes, applied in one statement across every matching row
* Add `Sortable::setLastForSortingPosition()` and the `sortingPositionBetween()` scope
* Add `Delimited::maxItemLength()` and its `item_length` translation
* Add `HasValidate::gainFloatValue()` and `gainArrayValue()`
* `Delimited`'s fluent setters return `static`, so subclasses keep their type

### Tests & tooling

* Rewrite `Pagination\PaginatedResourceArray` to extend `PaginatedResourceResponse` instead of
  copying it; a test compares the output against the framework's own on every run
* Raise line coverage from 49% to 93%, with a CI floor at 90%
* Run PHPStan in CI (it never ran before) at level 6 over `src` **and** `tests`, with Larastan
  and zero suppressions
* Extend PHPCS to `tests/`
* Add Redis to CI and `docker-compose`, and run the cacher suite against both phpredis and predis
* Add `.gitattributes` so tests, docs, CI and Docker stay out of the Composer archive
* Delete test fixtures left referencing the `Caster\HasCasts` removed in 4.0, and cover the
  sorting-restrictions branch they had left untested

### Documentation

* Rewrite `docs/` as a reference with signatures, recipes and links to the Laravel documentation
* Add a collected pitfalls page and an upgrade guide (4.0 claimed one that was never committed)
* Gate the documentation in CI: every symbol it names must exist with the signature shown, and
  every internal link must resolve

---

## [4.0.0](https://github.com/efureev/laravel-support/compare/v3.0.0...v4.0.0) (2026-06-04)

### ⚠ BREAKING CHANGES

* Require PHP >= 8.4 and Laravel >= 13
* Remove duplicate/obsolete functionality covered by Laravel 13: Caster (Geo/PgPoint/PgArray), LaraRequest, UUID trait and `user()` helper

### Features

* Upgrade to PHP 8.4 and Laravel 13 (promoted/readonly properties, strict types)
* Add Docker + docker-compose (PostgreSQL 18, pcov) test environment
* Migrate test suite to PHPUnit 13 (attributes, schema/env config)
* Modernize CI: dedicated PHPCS lint job, Composer cache, PostgreSQL 18 service, `softprops/action-gh-release`

### Bug Fixes

* `Delimited` rule: make it implicit so `min` rejects empty values; fix Postgres array casting in tests

### Documentation

* Split README/CHANGELOG into single sources, sync docs with current API, add upgrade guide

---

## [3.0.0](https://github.com/efureev/laravel-support/compare/v2.1.0...v3.0.0) (2025-02-24)

### Features

* Update to Laravel 12 ([65e065](https://github.com/efureev/laravel-support/commit/65e0659df86693b1abaf4bcdd799c9af803cacc6), [b3ad52](https://github.com/efureev/laravel-support/commit/b3ad525d331cb85ad122c2f9f3e69377eff62050))

---


## [2.0.0](https://github.com/efureev/laravel-support/compare/v1.19.1...v2.0.0) (2024-03-13)

### Features

* Add support Laravel 11 ([c6aab1](https://github.com/efureev/laravel-support/commit/c6aab17f0b62943849e182817d09214eab2626e8))


---

## [1.19.1](https://github.com/efureev/laravel-support/compare/v1.19.0...v1.19.1) (2023-08-04)

### Bug Fixes

* HasModelEntityCache.php ([112c01](https://github.com/efureev/laravel-support/commit/112c019baba08c5f8eb7e262548eef5c56cc8366), [f98d6d](https://github.com/efureev/laravel-support/commit/f98d6d5c916265568bbd40bfad60ec3b5be76805))


---

## [1.19.0](https://github.com/efureev/laravel-support/compare/v1.18.1...v1.19.0) (2023-08-04)

### Features

* Divide EntityCacheTrait ([81c8cb](https://github.com/efureev/laravel-support/commit/81c8cbb539ea1e3be846ddaec0cdbc0c632e2294))


---

## [1.18.1](https://github.com/efureev/laravel-support/compare/v1.18.0...v1.18.1) (2023-02-24)


---

## [1.18.0](https://github.com/efureev/laravel-support/compare/v1.17.1...v1.18.0) (2023-02-24)

### Features

* Add support Laravel 10 ([847a0a](https://github.com/efureev/laravel-support/commit/847a0a05aa5e673ef4cd1a7dff0da4119526777d))


---

## [1.17.1](https://github.com/efureev/laravel-support/compare/v1.17.0...v1.17.1) (2022-08-17)

### Features

* Add support >= Laravel 9.25 ([332a90](https://github.com/efureev/laravel-support/commit/332a901a2c52799ea84098637c8bd7e5e8e3f5e7))


---

## [1.17.0](https://github.com/efureev/laravel-support/compare/v1.16.5...v1.17.0) (2022-08-17)

### Features

* Add support `PHP 8.1` ([2d53f9](https://github.com/efureev/laravel-support/commit/2d53f9aad4ddb28a3f30af0bf3a9613e4a0ffa44))


---

## [1.16.5](https://github.com/efureev/laravel-support/compare/v1.16.4...v1.16.5) (2022-06-29)
### Bug Fixes

* CacheForgetByKey ([0e136b](https://github.com/efureev/laravel-support/commit/0e136ba5ee42555e4984ea09df828bc62d24d755))


---

## [1.16.3](https://github.com/efureev/laravel-support/compare/v1.16.2...v1.16.3) (2022-04-28)
### Features

* Add method getVersionFromFile to get version ([0c6cf9](https://github.com/efureev/laravel-support/commit/0c6cf917bc15e99b6769fe3c4c3e91d55c33156c), [11c66b](https://github.com/efureev/laravel-support/commit/11c66b124f0ca8592871c09952cde8fba2ec24e0))


---

## [1.16.2](https://github.com/efureev/laravel-support/compare/v1.16.1...v1.16.2) (2022-04-22)
### Bug Fixes

* CreateHttpRequests ([a4c5f1](https://github.com/efureev/laravel-support/commit/a4c5f1488ee50d1d159e73dac68624422e9aa858))


---

## [1.16.1](https://github.com/efureev/laravel-support/compare/v1.16.0...v1.16.1) (2022-04-22)
### Features

* Add some new funcs to CreateHttpRequests ([58a878](https://github.com/efureev/laravel-support/commit/58a878ac247529cfda98597a339278f0da37198b))


---

## [1.16.0](https://github.com/efureev/laravel-support/compare/v1.15.2...v1.16.0) (2022-04-22)
### Features

* Add trait `CreateHttpRequests` to `Test` ns ([a8d076](https://github.com/efureev/laravel-support/commit/a8d07600a421edc028171848ec3c379fe0ffe0d3))


---

## [1.15.2](https://github.com/efureev/laravel-support/compare/v1.15.1...v1.15.2) (2022-04-15)

---

## [1.15.1](https://github.com/efureev/laravel-support/compare/v1.15.0...v1.15.1) (2022-04-15)
### Bug Fixes

* Set sorting position as integer ([57043c](https://github.com/efureev/laravel-support/commit/57043ccddfb739cd0877c98fa297b44c8d70ecb9))


---

## [1.15.0](https://github.com/efureev/laravel-support/compare/v1.14.1...v1.15.0) (2022-03-23)
### Features

* Add the global function `objectToArray` ([1e1b15](https://github.com/efureev/laravel-support/commit/1e1b15bd40155041866e875d390b0563aed6713c))


---

## [1.14.1](https://github.com/efureev/laravel-support/compare/v1.14.0...v1.14.1) (2022-03-09)
### Features

* Policies: divide a logic ([423bff](https://github.com/efureev/laravel-support/commit/423bffec139cf9828ef6beeefcff2391573b030d))


---

## [1.14.0](https://github.com/efureev/laravel-support/compare/v1.13.3...v1.14.0) (2022-03-07)
### Features

* Add registerInstance ([e10615](https://github.com/efureev/laravel-support/commit/e106157eddcd24fef93462e2f0b961ff934c659c))


---

## [1.13.3](https://github.com/efureev/laravel-support/compare/v1.13.2...v1.13.3) (2022-03-04)

---

## [1.13.2](https://github.com/efureev/laravel-support/compare/v1.13.1...v1.13.2) (2022-03-04)
### Bug Fixes

* Cache ([500e3d](https://github.com/efureev/laravel-support/commit/500e3d1ff7e0dcbf67f7dd170f3e9836f9b828ce))


---

## [1.13.1](https://github.com/efureev/laravel-support/compare/v1.13.0...v1.13.1) (2022-02-21)
### Features

* Add afterInit method to LaraRequestServiceProvider ([7f5b24](https://github.com/efureev/laravel-support/commit/7f5b24cfe4bb26a41ca1e5ae3253c60ab165be9b))


---

## [1.13.0](https://github.com/efureev/laravel-support/compare/v1.12.2...v1.13.0) (2022-02-08)
### Features

* Add caster PgPoint ([328f30](https://github.com/efureev/laravel-support/commit/328f302068cf7e97131c5fa54768b703d1d195c1))


---

## [1.12.2](https://github.com/efureev/laravel-support/compare/v1.12.1...v1.12.2) (2022-02-03)

---

## [1.12.1](https://github.com/efureev/laravel-support/compare/v1.12.0...v1.12.1) (2022-02-03)

---

## [1.12.0](https://github.com/efureev/laravel-support/compare/v1.11.1...v1.12.0) (2022-02-03)
### Features

* Fix ServiceProviders ([b1af69](https://github.com/efureev/laravel-support/commit/b1af692b91365238ea9fc3573825bcd9c5be3844))


---

## [1.11.1](https://github.com/efureev/laravel-support/compare/v1.11.0...v1.11.1) (2022-01-21)
### Bug Fixes

* Caching ([2b5075](https://github.com/efureev/laravel-support/commit/2b5075224031f48b7880b55c49672f72e20fbf3e))


---

## [1.11.0](https://github.com/efureev/laravel-support/compare/v1.10.3...v1.11.0) (2022-01-18)
### Features

* Add HasModelEntityCache ([754117](https://github.com/efureev/laravel-support/commit/754117eb338e2034e228b498b2941686c594ca59))


---

## [1.10.3](https://github.com/efureev/laravel-support/compare/v1.10.2...v1.10.3) (2021-12-15)
### Bug Fixes

* LaraRequestServiceProvider ([2822de](https://github.com/efureev/laravel-support/commit/2822deb235c1272c68c8504ff057ce89c8b62cc2), [6d0b05](https://github.com/efureev/laravel-support/commit/6d0b05e6d5988fd9a68ef97d180ebe5b91e8fbd2))


---

## [1.10.2](https://github.com/efureev/laravel-support/compare/v1.10.1...v1.10.2) (2021-11-22)

---

## [1.10.1](https://github.com/efureev/laravel-support/compare/v1.10.0...v1.10.1) (2021-11-14)

---

## [1.10.0](https://github.com/efureev/laravel-support/compare/v1.9.0...v1.10.0) (2021-11-14)
### Features

* Add trait AllowToExecute ([0fda4d](https://github.com/efureev/laravel-support/commit/0fda4d0e83fcb38e1c699bf58c5a790379825644))


---

## [1.9.0](https://github.com/efureev/laravel-support/compare/v1.8.0...v1.9.0) (2021-10-16)

---

## [1.8.0](https://github.com/efureev/laravel-support/compare/v1.7.2...v1.8.0) (2021-10-07)

---

## [1.7.2](https://github.com/efureev/laravel-support/compare/v1.7.1...v1.7.2) (2021-09-29)

---

## [1.7.1](https://github.com/efureev/laravel-support/compare/v1.7.0...v1.7.1) (2021-09-29)
### Features

* Add hasDeferredRelation to DeferredRelations ([a2a237](https://github.com/efureev/laravel-support/commit/a2a2377f624d109809388bb3514783f10d0f2daa))


---

## [1.7.0](https://github.com/efureev/laravel-support/compare/v1.6.1...v1.7.0) (2021-09-29)
### Features

* Add model`s trait DeferredRelations ([3af639](https://github.com/efureev/laravel-support/commit/3af639f43f5d346916f777eb5dea0cdffa9c0ccf))


---

## [1.6.1](https://github.com/efureev/laravel-support/compare/v1.6.0...v1.6.1) (2021-09-21)

---

## [1.6.0](https://github.com/efureev/laravel-support/compare/v1.5.0...v1.6.0) (2021-08-27)
### Features

* Add some functions to HasValidate ([a95677](https://github.com/efureev/laravel-support/commit/a956778a62fa0829da182ba5120cb11a573956d6))


---

## [1.5.0](https://github.com/efureev/laravel-support/compare/v1.4.0...v1.5.0) (2021-08-11)
### Features

* Add HasMergeAdditional ([9992b6](https://github.com/efureev/laravel-support/commit/9992b67ddf072fd6029e35903e95de528246812b))


---

## [1.4.0](https://github.com/efureev/laravel-support/compare/v1.3.1...v1.4.0) (2021-07-30)
### Features

* Add GeoPoint caster ([c8f1e9](https://github.com/efureev/laravel-support/commit/c8f1e9dafac3820923a92b58fc331b2720da9c6b))


---

## [1.3.1](https://github.com/efureev/laravel-support/compare/v1.3.0...v1.3.1) (2021-07-29)

---

## [1.3.0](https://github.com/efureev/laravel-support/compare/v1.2.1...v1.3.0) (2021-07-29)
### Features

* Add ServiceProvider helpers ([84ae6f](https://github.com/efureev/laravel-support/commit/84ae6fc087bbe252cb3282e6bbd3b805d7f3c446))


---

## [1.2.1](https://github.com/efureev/laravel-support/compare/v1.2.0...v1.2.1) (2021-07-20)

---

## [1.2.0](https://github.com/efureev/laravel-support/compare/v1.1.0...v1.2.0) (2021-07-20)
### Features

* Add `validateValue` method ([29b085](https://github.com/efureev/laravel-support/commit/29b0857c37372fb555ffb12d6c2ee3186a014c08))


---

## [1.1.0](https://github.com/efureev/laravel-support/compare/v1.0.1...v1.1.0) (2021-05-25)

---

## [1.0.1](https://github.com/efureev/laravel-support/compare/v1.0.0...v1.0.1) (2021-05-24)

---

## [1.0.0](https://github.com/efureev/laravel-support/compare/v0.11.5...v1.0.0) (2021-04-28)

---

## [0.11.5](https://github.com/efureev/laravel-support/compare/v0.11.3...v0.11.5) (2021-02-16)

---

## [0.11.3](https://github.com/efureev/laravel-support/compare/v0.11.4...v0.11.3) (2021-02-04)

---

## [0.11.4](https://github.com/efureev/laravel-support/compare/v0.11.2...v0.11.4) (2021-02-04)

---

## [0.11.2](https://github.com/efureev/laravel-support/compare/v0.11.1...v0.11.2) (2021-01-29)

---

## [0.11.1](https://github.com/efureev/laravel-support/compare/v0.11.0...v0.11.1) (2021-01-28)

---

## [0.11.0](https://github.com/efureev/laravel-support/compare/v0.10.3...v0.11.0) (2021-01-27)

---

## [0.10.3](https://github.com/efureev/laravel-support/compare/v0.10.2...v0.10.3) (2021-01-26)

---

## [0.10.2](https://github.com/efureev/laravel-support/compare/v0.10.1...v0.10.2) (2021-01-25)

---

## [0.10.1](https://github.com/efureev/laravel-support/compare/v0.10.0...v0.10.1) (2021-01-25)

---

## [0.10.0](https://github.com/efureev/laravel-support/compare/v0.9.6...v0.10.0) (2021-01-25)

---

## [0.9.6](https://github.com/efureev/laravel-support/compare/v0.9.5...v0.9.6) (2020-12-28)

---

## [0.9.5](https://github.com/efureev/laravel-support/compare/v0.9.4.2...v0.9.5) (2020-11-06)

---

## [0.9.4.2](https://github.com/efureev/laravel-support/compare/v0.9.4.1...v0.9.4.2) (2020-09-16)

---

## [0.9.4.1](https://github.com/efureev/laravel-support/compare/v0.9.4...v0.9.4.1) (2020-09-16)

---

## [0.9.4](https://github.com/efureev/laravel-support/compare/v0.9.3.2...v0.9.4) (2020-09-16)

---

## [0.9.3.2](https://github.com/efureev/laravel-support/compare/v0.9.3.1...v0.9.3.2) (2020-09-10)

---

## [0.9.3.1](https://github.com/efureev/laravel-support/compare/v0.9.3...v0.9.3.1) (2020-09-07)

---

## [0.9.3](https://github.com/efureev/laravel-support/compare/v0.9.2...v0.9.3) (2020-09-07)

---

## [0.9.2](https://github.com/efureev/laravel-support/compare/v0.9.1...v0.9.2) (2020-08-14)

---

## [0.9.1](https://github.com/efureev/laravel-support/compare/v0.9.0...v0.9.1) (2020-08-12)

---

## [0.9.0](https://github.com/efureev/laravel-support/compare/v0.8.0...v0.9.0) (2020-08-12)

---

## [0.8.0](https://github.com/efureev/laravel-support/compare/v0.7.9...v0.8.0) (2020-07-08)

---

## [0.7.9](https://github.com/efureev/laravel-support/compare/v0.7.8...v0.7.9) (2020-07-06)

---

## [0.7.8](https://github.com/efureev/laravel-support/compare/v0.7.7...v0.7.8) (2020-07-06)

---

## [0.7.7](https://github.com/efureev/laravel-support/compare/v0.7.6...v0.7.7) (2020-07-06)

---

## [0.7.6](https://github.com/efureev/laravel-support/compare/v0.7.5...v0.7.6) (2020-07-03)

---

## [0.7.5](https://github.com/efureev/laravel-support/compare/v0.7.4...v0.7.5) (2020-07-02)

---

## [0.7.4](https://github.com/efureev/laravel-support/compare/v0.7.3...v0.7.4) (2020-06-24)

---

## [0.7.3](https://github.com/efureev/laravel-support/compare/v0.7.2...v0.7.3) (2020-06-23)

---

## [0.7.2](https://github.com/efureev/laravel-support/compare/v0.7.1...v0.7.2) (2020-06-23)

---

## [0.7.1](https://github.com/efureev/laravel-support/compare/v0.7.0...v0.7.1) (2020-06-23)

---

## [0.7.0](https://github.com/efureev/laravel-support/compare/v0.6.0...v0.7.0) (2020-06-22)

---

## [0.6.0](https://github.com/efureev/laravel-support/compare/v0.5.3...v0.6.0) (2020-06-15)

---

## [0.5.3](https://github.com/efureev/laravel-support/compare/v0.5.2...v0.5.3) (2020-05-24)

---

## [0.5.2](https://github.com/efureev/laravel-support/compare/v0.5.1...v0.5.2) (2020-05-24)

---

## [0.5.1](https://github.com/efureev/laravel-support/compare/v0.5.0...v0.5.1) (2020-05-24)

---

## [0.5.0](https://github.com/efureev/laravel-support/compare/v0.4.3...v0.5.0) (2020-05-23)

---

## [0.4.3](https://github.com/efureev/laravel-support/compare/v0.4.2...v0.4.3) (2020-05-23)

---

## [0.4.2](https://github.com/efureev/laravel-support/compare/v0.4.1...v0.4.2) (2020-04-10)

---

## [0.4.1](https://github.com/efureev/laravel-support/compare/v0.4.0...v0.4.1) (2020-04-06)

---

## [0.4.0](https://github.com/efureev/laravel-support/compare/v0.3.4...v0.4.0) (2020-04-04)

---

## [0.3.4](https://github.com/efureev/laravel-support/compare/v0.3.3...v0.3.4) (2020-03-05)

---

## [0.3.3](https://github.com/efureev/laravel-support/compare/v0.3.2...v0.3.3) (2020-02-25)

---

## [0.3.2](https://github.com/efureev/laravel-support/compare/v0.3.1...v0.3.2) (2020-02-19)

---

## [0.3.1](https://github.com/efureev/laravel-support/compare/v0.3.0...v0.3.1) (2020-02-19)

---

## [0.3.0](https://github.com/efureev/laravel-support/compare/v0.2.1...v0.3.0) (2020-02-18)

---

## [0.2.1](https://github.com/efureev/laravel-support/compare/v0.2.0...v0.2.1) (2020-02-11)

---

## [0.2.0](https://github.com/efureev/laravel-support/compare/v0.1.0...v0.2.0) (2020-02-06)

---

## [0.1.0](https://github.com/efureev/laravel-support/compare/v0.0.3...v0.1.0) (2020-01-29)

---

## [0.0.3](https://github.com/efureev/laravel-support/compare/v0.0.2...v0.0.3) (2020-01-28)

---

## [0.0.2](https://github.com/efureev/laravel-support/compare/v0.0.1...v0.0.2) (2020-01-28)

---

## [0.0.1](https://github.com/efureev/laravel-support/compare/19bad4a26e54f968db9f7c4efd7be1dc97712a0d...v0.0.1) (2020-01-28)

---

