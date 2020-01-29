# Changelog
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog][keepachangelog]
and this project adheres to [Semantic Versioning][semver].

## [unreleased]

## [0.1.0] - 2020-01-29
### Changed
  - Refactor `Caster` functionality.
  - Remove from the trait `PostgresArray` methods: `mutateToPgArray`, `accessPgArray`.

### Added
  - Written tests.

### Removed
  - The trait `CasterAttribute`.


## [0.0.3] - 2020-01-28
### Changed
  - Move the Trait `CasterAttribute` to different namespace: `Php\Support\Laravel\Traits\Models`.

## [0.0.2] - 2020-01-28
### Added
  - The trait for Model `PostgresArray`.

### Changed
  - Refactor: changed namespaces

## [0.0.1] - 2020-01-28
### Added
  - The trait `Caster`: for custom class casts. 
  - The trait `CasterAttribute`: for custom class casts. 
  - The trait for DB/Migration `UUID`. 

[unreleased]: https://github.com/efureev/laravel-support/compare/v0.0.2...HEAD
[0.1.0]: https://github.com/efureev/laravel-support/compare/v0.0.3...v0.1.0
[0.0.3]: https://github.com/efureev/laravel-support/compare/v0.0.2...v0.0.3
[0.0.2]: https://github.com/efureev/laravel-support/compare/v0.0.1...v0.0.2
[0.0.1]: https://github.com/efureev/laravel-support/releases/tag/v0.0.1

[keepachangelog]:https://keepachangelog.com/en/1.1.0/
[semver]:https://semver.org/spec/v2.0.0.html
