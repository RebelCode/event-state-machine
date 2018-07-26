# Change log
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/)
and this project adheres to [Semantic Versioning](http://semver.org/).

## [[*next-version*]] - YYYY-MM-DD
### Added
- The `EventStateMachineFactory` now accepts static event-related data to use for all created instances.

### Changed
- Optimized `EventStateMachineFactory` by no longer using callback factory abstraction.
- `EventStateMachine` now receives and uses an event factory to create transition events.

### Fixed
- Static event params given to `EventStateMachine` no longer override transition-related event params.

## [0.1-alpha1] - 2018-05-15
Initial version.
