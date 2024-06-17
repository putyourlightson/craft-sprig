# Release Notes for Sprig

## 3.1.0-beta.1 - 2024-06-17

### Added

- Added the [s-inherit](https://putyourlightson.com/plugins/sprig#s-inherit) attribute that allows you to control and enable automatic attribute inheritance for child nodes, if it has been disabled by default.

### Changed

- Updated htmx to version 2.0.0 ([release notes](https://htmx.org/posts/2024-06-17-htmx-2-0-0-is-released/)).

### Removed

- Removed the `s-sse` and `s-ws` attributes.

## 3.0.2 - 2024-04-21

### Changed

- Made it possible to pass a fully namespaced component class into the `sprig()` function ([#14](https://github.com/putyourlightson/craft-sprig-core/issues/14)).
- Updated htmx to version 1.9.12 ([changelog](https://github.com/bigskysoftware/htmx/blob/master/CHANGELOG.md#1912---2024-04-17)).

### Fixed

- Fixed a bug in which variables passed into a component were being converted to strings ([#369](https://github.com/putyourlightson/craft-sprig-core/issues/369)).

## 3.0.1 - 2024-04-21

### Fixed

- Fixed a bug in which some `sprig` variables were incorrectly persisting across requests ([#363](https://github.com/putyourlightson/craft-sprig/issues/363)).

## 3.0.0 - 2024-04-08

> {warning} Template caches and static page caches should be cleared after performing this update.

### Added

- Added compatibility with Craft 5.

### Changed

- Changed how the component configuration is encoded.

### Removed

- Removed the `sprig.script` variable.
- Removed the `s-on` attribute.
- Removed the `success` variable. Use `sprig.isSuccess` or `sprig.isError` instead.
- Removed the `flashes` variable. Use `sprig.message` instead.
- Removed the `id` variable. Use `sprig.modelId` instead.
