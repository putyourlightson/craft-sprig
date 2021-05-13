# Release Notes for Sprig

## 1.5.3 - Unreleased
### Fixed
- Fixed double encoding of ampersands in element attributes ([#133](https://github.com/putyourlightson/craft-sprig/issues/133)).

## 1.5.2 - 2021-04-09
### Fixed
- Fixed an error that could occur when registering a new user.

## 1.5.1 - 2021-04-09
### Changed
- Sprig overrides the `currentUser` global variable with a fresh version if the current user is updated using the `users/save-user` controller action ([#81](https://github.com/putyourlightson/craft-sprig/issues/81)).

## 1.5.0 - 2021-04-07
### Added
- Added the ability to prefix both `sprig` and `s-` attributes with `data-` for more valid HTML ([#117](https://github.com/putyourlightson/craft-sprig/issues/117)).
- Added the `hxDataPrefix` config setting that forces Sprig to use the `data-` prefix for `hx-` attributes.
- Added the [s-disable](https://putyourlightson.com/plugins/sprig#s-disable) attribute that disables htmx processing for a given element and its children.

### Changed
- Updated htmx to version 1.3.3 ([release notes](https://htmx.org/posts/2021-3-6-htmx-1.3.0-is-released/)).

## 1.4.0 - 2021-02-24
- Added the [s-headers](https://putyourlightson.com/plugins/sprig#s-headers) attribute that allows you to add to the headers that will be submitted with an AJAX request.

### Changed
- Updated htmx to version 1.2.1 ([release notes](https://htmx.org/posts/2021-2-13-htmx-1.2.0-is-released/)).

## 1.3.3 - 2021-02-03
### Changed
- Component classes are now created using the `createObject` method ([#93](https://github.com/putyourlightson/craft-sprig/issues/93)).
- Variables returned by controller actions called `variables` are now merged in to template variables ([#94](https://github.com/putyourlightson/craft-sprig/issues/94)).

## 1.3.2 - 2021-01-29
### Fixed
- Fixed compatibility with Craft pre version 3.5.0 ([#91](https://github.com/putyourlightson/craft-sprig/issues/91)).

## 1.3.1 - 2021-01-13
### Fixed
- Fixed missing variable methods ([#79](https://github.com/putyourlightson/craft-sprig/issues/79)).

## 1.3.0 - 2021-01-06
### Added
- Added the following return variables when controller actions are called.
    - `success` (boolean) whether the action succeeded.
    - `flashes` (array) flash messages set by the action, keyed by type (`notice` or `error`).
    - `id` (integer) the ID of the model if one was successfully created/updated.
    - `modelName` (model) a model that failed validation, for example `entry` when `entries/save-entry` fails or `user` when `users/save-user` fails.
- Added a new [s-preserve](https://putyourlightson.com/plugins/sprig#s-preserve) attribute that ensures that an element remains unchanged even when the component is re-rendered.
- Added a [subresource integrity](https://developer.mozilla.org/en-US/docs/Web/Security/Subresource_Integrity) attribute to the htmx script tag when fetching from a CDN for added security.

### Changed
- Updated htmx to version 1.1.0 ([release notes](https://htmx.org/posts/2021-1-6-htmx-1.1.0-is-released/)).
- Requires Craft 3.1.19 or higher.

### Removed
- The `sprig.hyperscript` function has been removed. Use the [htmx JS API](https://htmx.org/reference/#api) or [import hyperscript](https://hyperscript.org/docs/) in your templates manually instead.

### Deprecated
- The `errors` variable has been deprecation. Use the `getErrors()` method on the model that is returned when validation fails.

```twig
{# OLD way of handling errors #}
{% if errors.title is defined %}
    {{ errors.title|first }}
{% endif

{# NEW way of handling errors (assumes `entry` failed validation) #}
{% if entry.hasErrors('title') %}
    {{ entry.getFirstError('title') }}
{% endif %}
```

- The `sprig.element`, `sprig.elementName`, `sprig.elementValue` and `sprig.eventTarget` tags have been deprecated, will each return a blank string since being removed from htmx 1.1.0, and should be removed from templates.

## 1.2.0 - 2020-12-14
### Added
- Added a new [s-encoding](https://putyourlightson.com/plugins/sprig#s-encoding) attribute that can be used to set the encoding of requests to `multipart/form-data` for file uploads ([#9](https://github.com/putyourlightson/craft-sprig/issues/9)).

### Changed
- Updated htmx to version 1.0.2 ([release notes](https://github.com/bigskysoftware/htmx/blob/master/CHANGELOG.md#102---2020-12-12)).

### Deprecated
- The `sprig.hyperscript` function has been deprecated. Use the [htmx JS API](https://htmx.org/reference/#api) or [import hyperscript](https://hyperscript.org/docs/) in your templates manually instead.

## 1.1.1 - 2020-11-24
### Changed
- Updated htmx to version 1.0.0 ([release notes](https://htmx.org/posts/2020-11-24-htmx-1.0.0-is-released/)).

### Fixed
- Fixed a bug that was preventing the `s-push-url` attribute from being parsed ([#62](https://github.com/putyourlightson/craft-sprig/issues/62)).

## 1.1.0 - 2020-11-23
> {warning} The `s-vars` attribute has been deprecated for security reasons, use the new `s-vals` or `s-val:*` attribute instead (read the details [here](https://putyourlightson.com/articles/sprig-1-1-0-released)). The magic `_url` and `_events` variables have been removed, use the new `sprig.pushUrl()` and `sprig.triggerEvents()` functions instead.

### Added
- Added new [s-vals](https://putyourlightson.com/plugins/sprig#s-vals) and [s-val:*](https://putyourlightson.com/plugins/sprig#s-val) attributes that add values to a request and that should be used instead of the deprecated [s-vars](https://putyourlightson.com/plugins/sprig#s-vars).
- Added a new [s-replace](https://putyourlightson.com/plugins/sprig#s-replace) attribute that replaces only the specified element in the component. 
- Added a [sprig.paginate()](https://putyourlightson.com/plugins/sprig#sprig.paginateelementquery-page) template variable that paginates an element query.
- Added a [sprig.pushUrl()](https://putyourlightson.com/plugins/sprig#sprig.pushurlurl) template variable that pushes a URL into the history stack.
- Added a [sprig.redirect()](https://putyourlightson.com/plugins/sprig#sprig.redirecturl) template variable that redirects the browser to a URL.
- Added a [sprig.refresh()](https://putyourlightson.com/plugins/sprig#sprig.refresh) template variable that refreshes the browser.
- Added a [sprig.triggerEvents()](https://putyourlightson.com/plugins/sprig#sprig.triggereventsevents) template variable that triggers client-side events.

### Changed
- Updated htmx to version 0.4.0 ([release notes](https://htmx.org/posts/2020-11-16-htmx-0.4.0-is-released/)).
- Removed the magic `_url` variable, use the new `sprig.pushUrl()` function instead.
- Removed the magic `_events` variable, use the new `sprig.triggerEvents()` function instead.
- Made minor tweaks to default playground.

### Fixed
- Fixed how array variables are handled in the playground.

### Deprecated
- The `s-vars` attribute has been deprecated for security reasons. Use the new `s-vals` or `s-val:*` attribute instead.

### Security
- Fixed a potential XSS vulnerability.

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
- Added the Sprig playground.
- Added a new Sprig logo.

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
