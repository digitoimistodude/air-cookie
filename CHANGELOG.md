# Changelog
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/), and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]
### Added
* Filters to modify inline scripts

## [1.2.1] - 2023-06-28
### Changed
* Update CookieConsent to version 2.9.1

## [1.2.0] - 2022-08-24
### Changed
* Update CookieConsent to version 2.8.5

### Added
* Elements with `data-aircookie-accept` attribute are listened for clicks, causing the category specified in the value to be accepted
* Elements with `data-aircookie-remove-on` attribute are removed when the category specified is accepted

## [1.1.4] - 2021-12-14

### Fixed
* Fix JS loading issue by changing the inject priority

## [1.1.3] - 2021-12-08

### Fixed
- Fix JS error, undefined manager after embed group is accepted
- Fix JS error, undefined CC element after consent

## [1.1.2] - 2021-11-01

### Fixed
- Fix string register if Polylang is not active

## [1.1.1] - 2021-10-07

### Fixed
- Cookie expiry time saving

## [1.1.0] - 2021-10-07

### Added
- Run the cookie category JS also when user changes the cookie settings (CookieConsent onChange event)

### Changed
- Updated CookieConsent to version 2.6.0
- Save the visitor id on main cookie

### Removed
- Functionality related to handling the visitor id in separate cookie

## [1.0.0] - 2021-09-29

First release.
