# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog][keepachangelog]
and this project adheres to [Semantic Versioning][semver].

Check MD [online][check-online].

## [unreleased]

## [1.9.0] - 2021-10-16

### Added

- Add some methods to `PostgresArray` trait:
  - `scopeWherePgArrayContainsAny`
  - `scopeWherePgArrayContainsOnly`
  - `scopeWherePgArrayOverlapWith`

## [1.7.2] - 2021-09-29

### Removed

- Remove model's trait `DeferredRelations`. Use `Model::$relationResolvers`

## [1.7.0] - 2021-09-29

### Added

- Add model's trait `DeferredRelations`

## [1.6.0] - 2021-08-27

### Added

- Add some functions to the `HasValidate`:
  - `gainIntValue`
  - `gainStringValue`
  - `gainBoolValue`

## [1.3.0] - 2021-07-29

### Added

- Add helpers to work with `ServiceProvider`:
  - class `AbstractServiceProvider`
  - trait `HasBooting`
  - trait `HasCommands`
  - trait `HasPolicies`
  - trait `HasPathHelpers`
  - trait `HasRegisters`

## [1.2.0] - 2021-07-20

### Added

- Add a method `validateValue` to trait `HasValidate`

## [1.1.0] - 2021-05-25

### Added

- Add a method `value` to the interface `Caster`

## [1.0.0] - 2021-04-28

### Changed

- Create a new stage of the package with minimal PHP version: `8.0`

## [0.11.0] - 2021-01-27

### Removed

- Move all `Laravel Database/Scheme` code to new package `efureev/laravel-support-db`

## [0.10.1] - 2021-01-25

### Added

- Added Global Scope to `Sortable`

## [0.10.0] - 2021-01-25

### Changed

- Completely rewritten the code of `Sortable`

## [0.9.4] - 2020-09-16

### Added

- Added class `LaraRequest` for building custom requests

## [0.9.3] - 2020-09-07

### Added

- Added class `AbstractRepository` for building custom repositories

## [0.9.2] - 2020-08-14

### Fixed

- Fixed `isDirty` and `getDirty` into Eloquent Model with `Caster` interface

## [0.9.1] - 2020-08-13

### Added

- Add trait `RequestModelable`

## [0.9.0] - 2020-08-13

### Added

- Add trait `Modelable`
- Add trait `ModelQueryable`

## [0.8.0] - 2020-07-08

### Added

- Add trait `WrapQuery`

## [0.7.4] - 2020-06-24

### Added

- Add global function `toCollect`

## [0.7.1] - 2020-06-23

### Added

- Add `AbstractCastingCollection`

## [0.7.0] - 2020-06-22

### Added

- Add `PaginatedResourceArray`

## [0.6.0] - 2020-06-15

### Added

- Add global method: `user`

## [0.5.3] - 2020-05-24

### Changed

- Caster: change public to protected

## [0.5.2] - 2020-05-24

### Changed

- Caster
- Minimum stability: `Laravel` >=7.0 && `PHP` >=7.3

## [0.3.0] - 2020-02-18

### Added

- Rules: `Authorized`, `Delimited`

## [0.2.1] - 2020-02-11

### Changed

- Sortable: refactor

## [0.2.0] - 2020-02-06

### Added

- Sortable traits: Sorting models in list

## [0.1.0] - 2020-01-29

### Changed

- Refactor `Caster` functionality
- Remove from the trait `PostgresArray` methods: `mutateToPgArray`, `accessPgArray`

### Added

- Written tests

### Removed

- The trait `CasterAttribute`

## [0.0.3] - 2020-01-28

### Changed

- Move the Trait `CasterAttribute` to different namespace: `Php\Support\Laravel\Traits\Models`

## [0.0.2] - 2020-01-28

### Added

- The trait for Model `PostgresArray`

### Changed

- Refactor: changed namespaces

## [0.0.1] - 2020-01-28

### Added

- The trait `Caster`: for custom class casts
- The trait `CasterAttribute`: for custom class casts
- The trait for DB/Migration `UUID`

[unreleased]: https://github.com/efureev/laravel-support/compare/v1.6.0...HEAD

[1.6.0]: https://github.com/efureev/laravel-support/compare/v1.0.0...v1.6.0

[1.0.0]: https://github.com/efureev/laravel-support/compare/v0.11.0...v1.0.0

[0.11.0]: https://github.com/efureev/laravel-support/compare/v0.10.1...v0.11.0

[0.10.1]: https://github.com/efureev/laravel-support/compare/v0.10.0...v0.10.1

[0.10.0]: https://github.com/efureev/laravel-support/compare/v0.9.4...v0.10.0

[0.9.4]: https://github.com/efureev/laravel-support/compare/v0.9.3...v0.9.4

[0.9.3]: https://github.com/efureev/laravel-support/compare/v0.9.2...v0.9.3

[0.9.2]: https://github.com/efureev/laravel-support/compare/v0.9.0...v0.9.2

[0.8.0]: https://github.com/efureev/laravel-support/compare/v0.8.0...v0.9.0

[0.8.0]: https://github.com/efureev/laravel-support/compare/v0.7.0...v0.8.0

[0.7.0]: https://github.com/efureev/laravel-support/compare/v0.6.0...v0.7.0

[0.6.0]: https://github.com/efureev/laravel-support/compare/v0.5.3...v0.6.0

[0.5.3]: https://github.com/efureev/laravel-support/compare/v0.5.2...v0.5.3

[0.5.2]: https://github.com/efureev/laravel-support/compare/v0.3.0...v0.5.2

[0.3.0]: https://github.com/efureev/laravel-support/compare/v0.2.1...v0.3.0

[0.2.1]: https://github.com/efureev/laravel-support/compare/v0.2.0...v0.2.1

[0.2.0]: https://github.com/efureev/laravel-support/compare/v0.1.0...v0.2.0

[0.1.0]: https://github.com/efureev/laravel-support/compare/v0.0.3...v0.1.0

[0.0.3]: https://github.com/efureev/laravel-support/compare/v0.0.2...v0.0.3

[0.0.2]: https://github.com/efureev/laravel-support/compare/v0.0.1...v0.0.2

[0.0.1]: https://github.com/efureev/laravel-support/releases/tag/v0.0.1

[keepachangelog]:https://keepachangelog.com/en/1.1.0/

[semver]:https://semver.org/spec/v2.0.0.html

[check-online]:https://dlaa.me/markdownlint
