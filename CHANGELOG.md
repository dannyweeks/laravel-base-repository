# Change Log

All notable changes to this project will be documented in this file.
This project adheres to [Semantic Versioning](http://semver.org/).

## Unreleased
dust..

## [1.0.0] - 2016-03-05
### Added
- Optional extra functionality. Add the trait to your repositories and let me do the rest
    - Results caching. Just add `use \Weeks\Laravel\Repositories\Traits\CacheResults;`.
    - Throw HTTP exceptions when appropriate. Just add `use \Weeks\Laravel\Repositories\Traits\ThrowsHttpExceptions;`.
- Eloquent integration tests.
- Cache trait tests.
- Http trait tests.

### Changed
- Include composer.lock file in VC.
- All methods 'get' type methods are wrapped in doQuery method.

### Fixed
- PSR-2 compliance.

## [0.1.1] - 2015-10-22
### Fixed
- Allow access to the requested relationship array in children classes.

## [0.1.0] - 2015-10-22
### Fixed
- First release.