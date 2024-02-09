# Release Notes for Sprig

## 3.0.0 - Unreleased

> {warning} Flash messages are no longer returned when calling controller actions. The `message` variable should be used instead.

### Added

- Added compatibility with Craft 5.0.0.

### Changed

- Flash messages are no longer returned when calling controller actions. The `message` variable should be used instead.
- Requests that accept JSON responses are now used when running controller actions ([#301](https://github.com/putyourlightson/craft-sprig/issues/301)).
