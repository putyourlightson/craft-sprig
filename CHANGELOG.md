# Release Notes for Sprig

## 1.1.0 - Unreleased
> {warning} The `s-vars` attribute has been deprecated for security reasons, use the new `s-val-*` attribute or the `sprig.vals()` function instead. The magic `_url` and `_events` variables have been removed, use the new `sprig.pushUrl()` and `sprig.triggerEvents()` functions instead.

### Added
- Added a new [s-val-*](https://putyourlightson.com/plugins/sprig#s-val-*) attribute that adds a value to a request and that should be used instead of the deprecated [s-vars](https://putyourlightson.com/plugins/sprig#s-vars).
- Added a [sprig.paginate()](https://putyourlightson.com/plugins/sprig#sprig.paginateelementquery) template variable that paginates an element query.
- Added a [sprig.pushUrl()](https://putyourlightson.com/plugins/sprig#sprig.pushurlurl) template variable that pushes a URL into the history stack.
- Added a [sprig.redirect()](https://putyourlightson.com/plugins/sprig#sprig.redirecturl) template variable that redirects the browser to a URL.
- Added a [sprig.refresh()](https://putyourlightson.com/plugins/sprig#sprig.refresh) template variable that refreshes the browser.
- Added a [sprig.triggerEvents()](https://putyourlightson.com/plugins/sprig#sprig.triggereventsevents) template variable that triggers client-side events.
- Added a [sprig.vals()](https://putyourlightson.com/plugins/sprig#sprig.valsvalues) template variable that adds values to a request and that should be used instead of the deprecated [s-vars](https://putyourlightson.com/plugins/sprig#s-vars).

### Changed
- Updated htmx to version 0.4.0 ([release notes](https://htmx.org/posts/2020-11-16-htmx-0.4.0-is-released/)).
- Removed the magic `_url` variable, use the new `sprig.pushUrl()` function instead.
- Removed the magic `_events` variable, use the new `sprig.triggerEvents()` function instead.
- Made minor tweaks to default playground.

### Fixed
- Fixed how array variables are handled in the playground.

### Deprecated
- The `s-vars` attribute has been deprecated for security reasons. Use the new `s-val-*` attribute or the `sprig.vals()` function instead.

## 1.0.3 - 2020-10-28
### Added
- Added a config setting to enable or disable the playground on a per environment basis.

## 1.0.2 - 2020-10-22
### Added
- Added a warning message if `devMode` is not enabled.

### Changed
- Updated htmx to version 0.2.0 ([release notes](https://htmx.org/posts/2020-9-30-htmx-0.2.0-is-released/)).

## 1.0.1 - 2020-10-21
- Migration schema version release for beta updates.

## 1.0.0 - 2020-10-21
### Added
- Added Sprig playground.
- Added new Sprig logo.

## 1.0.0-beta.18.1 - 2020-10-19
### Fixed
- Fixed an issue with the schema version in project config ([#42](https://github.com/putyourlightson/craft-sprig/issues/42)).

## 1.0.0-beta.18 - 2020-10-16
### Added
- Added `beforeCreateComponent` and `afterCreateComponent` events to `ComponentsService`.

### Changed
- Duplicate IDs in components no longer throw an error ([#40](https://github.com/putyourlightson/craft-sprig/issues/40)).
- Removed the `sprig.request` template variable, use `sprig.isRequest` instead.
- Removed the `sprig.include` template variable, use `sprig.isInclude` instead.

## 1.0.0-beta.17 - 2020-09-25
### Changed
- Uses a HTML5 compatible DOM parser.

### Fixed 
- Fixed a bug in which HTML tags would be stripped when inside of `<script>` tags ([#34](https://github.com/putyourlightson/craft-sprig/issues/34)).

## 1.0.0-beta.16 - 2020-09-18
### Changed
- Uses htmx 0.1.2 and hyperscript 0.0.2 ([release notes](https://htmx.org/posts/2020-9-18-htmx-0.1.0-is-released/)).

## 1.0.0-beta.15 - 2020-09-14
### Changed
- Controller actions that return models are now dealt with accordingly.

### Deprecated
- The `sprig.include` template variable has been deprecated and will be removed in version 1.0.0. Use `sprig.isInclude` instead.
- The `sprig.request` template variable has been deprecated and will be removed in version 1.0.0. Use `sprig.isRequest` instead.

## 1.0.0-beta.14 - 2020-09-10
### Changed
- Requests to controller actions are now forced to be AJAX requests.

### Fixed
- Fixed an error that could occur if a controller action did not return a JSON response ([#26](https://github.com/putyourlightson/craft-sprig/issues/26)).

## 1.0.0-beta.13 - 2020-09-03
### Added
- Added the `sprig.hyperscript` tag.

### Changed 
- Invalid variable exceptions include suggestions for how code could be fixed.

## 1.0.0-beta.12 - 2020-09-01
### Added
- Added a default `class="sprig-component"` to all components.

### Changed 
- An exception is now thrown if an array, object or value that cannot be converted to a string is passed into a Sprig component as a variable.

## 1.0.0-beta.11 - 2020-08-18
> {warning} The ability to override the component trigger using the `trigger` attribute has been removed. Use `s-trigger` instead.
 
### Added
- Added the ability to override all `s-` attributes on the component.

### Changed 
- Removed the ability to override the component trigger using the `trigger` attribute.

## 1.0.0-beta.10 - 2020-08-04
### Added
- Added the ability to override the component trigger using the `trigger` attribute.

### Changed
- CSRF tokens are now added as `vars` instead of input fields ([#6](https://github.com/putyourlightson/craft-sprig/issues/6)).
- The current site is now maintained when rendering components ([#13](https://github.com/putyourlightson/craft-sprig/issues/13)).

## 1.0.0-beta.9 - 2020-07-21
### Added
- Added support for nesting components ([#6](https://github.com/putyourlightson/craft-sprig/issues/6)).
- Added a `refresh` event trigger to components.

### Changed
- Request parameters are not hashed as variables when a component is created initially ([#5](https://github.com/putyourlightson/craft-sprig/issues/5)).

## 1.0.0-beta.8 - 2020-07-09
### Added
- Added the `sprig.htmxVersion` variable back in.
- Added unit tests to ensure that both local and remote versions of htmx exist.

### Changed
- Requires htmx 0.0.8 ([release notes](https://htmx.org/posts/2020-7-8-htmx-0.0.8-is-released/)).
- Uses the htmx script from unpkg.com unless in a `dev` environment, in which case it uses a local version.

## 1.0.0-beta.7 - 2020-07-06
### Added
- Added unit tests.

### Changed
- Returns a 400 error when submitted data is determined to be tampered.
- Replaced htmx script from unpkg.com with a local version.
- Removed the `sprig.htmxVersion` variable.

## 1.0.0-beta.6 - 2020-07-03
### Added
- Added the `sprig.htmxVersion` variable.

### Changed
- Request parameters are now added as variables to the initial sprig include.
- Removed the ability to load extensions using the `sprig.script` tag.

## 1.0.0-beta.5 - 2020-07-01
### Added
- Added the ability to pass protected variables into components by prefixing them with an underscore.

### Changed
- Requires htmx 0.0.7 ([release notes](https://htmx.org/posts/2020-6-30-htmx-0.0.7-is-released/)).

## 1.0.0-beta.4 - 2020-06-29
### Added
- Added the `s-vars` attribute.

### Fixed
- Fixed characters not being encoded in UTF-8 in rendered components ([#2](https://github.com/putyourlightson/craft-sprig/issues/2)).
- Fixed a bug that could throw an error when creating a component class.

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
- Requires htmx 0.0.6 ([release notes](https://htmx.org/posts/2020-6-20-htmx-0.0.6-is-released/)).

## 1.0.0-beta.1 - 2020-06-15
- Initial release.
