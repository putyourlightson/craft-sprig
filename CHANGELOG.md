# Release Notes for Sprig

## 1.0.0-beta.4 - Unreleased
### Fixed
- Fixed characters not being encoded in UTF-8 in rendered components ([#2](https://github.com/putyourlightson/craft-sprig/issues/2)).

## 1.0.0-beta.3 - 2020-06-26
### Added
- Added the ability to send events and a URL back in the response header.
- Added the ability to load extensions using the `sprig.script` tag.

### Changed
- Renamed the protected property `template` to `_template` in the component class.
- Removed the `error-url` from the list of available attributes since it was dropped in htmx 0.0.6.

## 1.0.0-beta.2 - 2020-06-21
### Added
- Added the `sprig.include` variable.

### Changed
- Require htmx 0.0.6.

## 1.0.0-beta.1 - 2020-06-15
- Initial release.
