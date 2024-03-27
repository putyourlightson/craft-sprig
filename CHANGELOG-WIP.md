# Release Notes for Sprig

## 3.0.0 - Unreleased

> {warning} Template caches and static page caches should be cleared after performing this update.

### Added

- Added compatibility with Craft 5.0.0.

### Changed

- Changed how the component configuration is encoded.

### Removed

- Removed the `sprig.script` variable.
- Removed the `s-on` attribute.
- Removed the `success` variable. Use `sprig.isSuccess` or `sprig.isError` instead.
- Removed the `flashes` variable. Use `sprig.message` or `craft.app.session.flash()` instead.
- Removed the `id` variable. Use `sprig.modelId` instead.
