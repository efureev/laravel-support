# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog][keepachangelog]
and this project adheres to [Semantic Versioning][semver].

Check MD [online][check-online].

## [unreleased]

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

[unreleased]: https://github.com/efureev/laravel-support/compare/v0.6.0...HEAD
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
