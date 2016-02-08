# Change Log

All notable changes to this project will be documented in this file.
This project adheres to [Semantic Versioning](http://semver.org/).

## Unreleased
### Added
- Results caching. Just add `use \Weeks\Laravel\Repositories\CacheResults;` to your repositories and let Laravel do the rest.
- Eloquent integration tests.
- Cache trait tests.

### Changed
- Include composer.lock file in VC.

### Fixed
- PSR-2 compliance.

## [0.1.1] - 2015-10-22
### Fixed
- Allow access to the requested relationship array in children classes.

## [0.1.0] - 2015-10-22
### Fixed
- First release.