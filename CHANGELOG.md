# Release Notes for Sprig

## 3.0.0-beta.1 - Unreleased

> {warning} Template caches and static page caches should be cleared after performing this update.

### Added

- Added compatibility with Craft 5.0.0.
- Added the `sprig.isSuccess` variable.
- Added the `sprig.isError` variable.
- Added the `sprig.message` variable.
- Added the `sprig.modelId` variable.

### Changed

- Changed how the component configuration is encoded.

### Removed

- Removed the `sprig.script` variable.
- Removed the `s-on` attribute.

### Deprecated

- Deprecated the `success` variable. Use `sprig.isSuccess` or `sprig.isError` instead.
- Deprecated the `flashes` variable. Use `sprig.message` or `craft.app.session.flash()` instead.
- Deprecated the `id` variable. Use `sprig.modelId` instead.
