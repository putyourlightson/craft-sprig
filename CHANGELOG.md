# Release Notes for Sprig

## 2.8.0 - 2024-03-26

### Added

- Added the `sprig.isSuccess` variable.
- Added the `sprig.isError` variable.
- Added the `sprig.message` variable.
- Added the `sprig.modelId` variable.

### Deprecated

- Deprecated the `success` variable. Use `sprig.isSuccess` or `sprig.isError` instead.
- Deprecated the `flashes` variable. Use `sprig.message` instead.
- Deprecated the `id` variable. Use `sprig.modelId` instead.

## 2.7.3 - 2023-12-12

### Added

- Added the ability to pass script tag attributes via the `sprig.registerScript()` and `sprig.setRegisterScript()` functions ([#338](https://github.com/putyourlightson/craft-sprig/issues/338)).
- Added the `ComponentsService::setConfig()` method.

### Changed

- Updated htmx to version 1.9.9 ([changelog](https://github.com/bigskysoftware/htmx/blob/master/CHANGELOG.md#199---2023-11-21)).

## 2.7.2 - 2023-09-29

### Added

- Added the [`sprig.registerScript()`](https://putyourlightson.com/plugins/sprig#sprig-registerscript) function ([#329](https://github.com/putyourlightson/craft-sprig/issues/329)).

### Changed

- Renamed the `sprig.setAddScript()` function to `sprig.setRegisterScript()`.

## 2.7.1 - 2023-09-27

### Fixed

- Fixed a deprecated tag in the Sprig playground.

## 2.7.0 - 2023-09-26

> {warning} The htmx script is now automatically injected into the end of the page whenever a Sprig component is created. If you have any JavaScript code in your layouts that rely on htmx being loaded, you will need to wrap them in `{% js %}` tags as per the [docs](https://putyourlightson.com/plugins/sprig#javascript).

### Added

- Added the Sprig component generator that scaffolds PHP components via a console command (`php craft make sprig-component --path sprig/components`).
- Added the [s-cache](https://putyourlightson.com/plugins/sprig#s-cache) attribute that allows you to specify if and for how long a request should be cached locally in the browser.
- Added the [s-on:*](https://putyourlightson.com/plugins/sprig#s-on) attribute that allows you to respond to events directly on an element.
- Added the [s-disabled-elt](https://putyourlightson.com/plugins/sprig#s-disabled-elt) attribute that allows you to specify elements that will have the disabled attribute added to them for the duration of the request.
- Added the `sprig.htmxVersion` function.
- Added the `sprig.setAddScript()` function.
- Added friendly invalid variable exceptions that are shown when the [Canary](https://plugins.craftcms.com/canary) plugin is installed.

### Changed

- The htmx script is now automatically injected into the end of the page whenever a Sprig component is created, meaning that the `sprig.script` function is no longer required and can be safely removed.
- Updated htmx to version 1.9.6 ([changelog](https://github.com/bigskysoftware/htmx/blob/master/CHANGELOG.md#196---2023-09-22)).
- Simplified and improved invalid variable error messages.
- Invalid variable error messages are now only shown when `devMode` is turned on.

### Fixed

- Fixed the response status code that is sent when required request parameters are not supplied ([#325](https://github.com/putyourlightson/craft-sprig/issues/325)).
- Fixed a potential security issue.

### Deprecated

- Deprecated the `sprig.script` function. It is no longer required and can be safely removed.
- Deprecated the `s-on` attribute. Use the `s-on:*` attribute instead.

## 2.6.2 - 2023-05.01

### Changed

- Updated htmx to version 1.9.2 ([changelog](https://github.com/bigskysoftware/htmx/blob/master/CHANGELOG.md#192---2023-04-28)).
- Improved autocomplete documentation in the playground.

## 2.6.1 - 2023-04-25

### Fixed

- Fixed a bug in which the htmx file was not being published even if it did not already exist locally ([#305](https://github.com/putyourlightson/craft-sprig/issues/305)).

## 2.6.0 - 2023-04-23

### Added

- Added the [sprig.setConfig](https://putyourlightson.com/plugins/sprig#sprig-setconfigoptions) template variable that allows you to set [configuration options](https://htmx.org/docs/#config) for htmx (via a meta tag).
- Added the [s-on](https://putyourlightson.com/plugins/sprig#s-on) attribute that allows you to respond to events directly on an element.

### Changed

- Updated htmx to version 1.9.0 ([release notes](https://htmx.org/posts/2023-04-11-htmx-1-9-0-is-released/)).
- The htmx file is now loaded locally rather than from a CDN, to reduce dependency on third-party sites ([#303](https://github.com/putyourlightson/craft-sprig/issues/303)).

## 2.5.1 - 2023-01-31

### Fixed

- Fixed rendered markup not being escaped in the Sprig playground.

## 2.5.0 - 2023-01-18

### Added

- Added the [s-history](https://putyourlightson.com/plugins/sprig#s-history) attribute that prevents sensitive data being saved to the history cache.

### Changed

- Updated htmx to version 1.8.5 ([release notes](https://htmx.org/posts/2023-01-17-htmx-1.8.5-is-released/)).

## 2.4.0 - 2022-12-08

### Added

- Added the [sprig.triggerRefreshOnLoad](https://putyourlightson.com/plugins/sprig#sprig-triggerrefreshonloadselector) template variable that triggers a refresh event on all components on page load ([#279](https://github.com/putyourlightson/craft-sprig/issues/279)).

## 2.3.0 - 2022-11-07

### Added

- Added the [s-validate](https://putyourlightson.com/plugins/sprig#s-validate) attribute that forces an element to validate itself before it submits a request.
- Added the [sprig.isHistoryRestoreRequest](https://putyourlightson.com/plugins/sprig#sprig-ishistoryrestorerequest) template variable that returns whether the request is for history restoration after a miss in the local history cache a client-side redirect without reloading the page.

### Changed

- Updated htmx to version 1.8.4 ([changelog](https://github.com/bigskysoftware/htmx/blob/master/CHANGELOG.md#184---2022-11-05)).
- Swapped out the Twigfield package for the Code Editor package ([#278](https://github.com/putyourlightson/craft-sprig/issues/278)).
- Improved the autocomplete API.

## 2.2.1 - 2022-10-08

### Fixed

- Fixed a bug in which Sprig requests were failing in live preview requests ([#269](https://github.com/putyourlightson/craft-sprig/issues/269)).

## 2.2.0 - 2022-10-05

### Added

- Added the [s-replace-url](https://putyourlightson.com/plugins/sprig#s-replace-url) attribute that allows you to replace the current url of the browser location history.
- Added the [s-select-oob](https://putyourlightson.com/plugins/sprig#s-select-oob) attribute that selects one or more elements from a server response to swap in via an out-of-band swap.
- Added the [sprig.location(url)](https://putyourlightson.com/plugins/sprig#sprig-locationurl) template variable that triggers a client-side redirect without reloading the page.
- Added the [sprig.replaceUrl(url)](https://putyourlightson.com/plugins/sprig#sprig-replaceurlurl) template variable that replaces the current URL in the location bar.
- Added the [sprig.reswap(value)](https://putyourlightson.com/plugins/sprig#sprig-reswapvalue) template variable that allows you to change the swap behaviour.

### Changed

- Updated htmx to version 1.8.0 ([release notes](https://htmx.org/posts/2022-07-12-htmx-1.8.0-is-released/)).

## 2.1.0 - 2022-06-22

### Added

- Added the Twigfield package that adds full autocomplete to the code editor in the playground ([#235](https://github.com/putyourlightson/craft-sprig/pull/235) ❤️@nystudio107).

### Changed

- Improved attribute autocomplete in the playground.

## 2.0.1 - 2022-05-12

### Changed

- Improved the styling of variables panes in the playground.

## 2.0.0 - 2022-05-04

### Added

- Added compatibility with Craft 4.

### Changed

- Restyled invalid variable error messages.

### Removed

- Removed the deprecated `sprig.element`, `sprig.elementName`, `sprig.elementValue` and `sprig.eventTarget` tags.
- Removed the deprecated `s-vars` attribute.
