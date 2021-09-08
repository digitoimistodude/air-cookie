# Air cookie

[![Packagist](https://img.shields.io/packagist/v/digitoimistodude/air-cookie.svg?style=flat-square)](https://packagist.org/packages/digitoimistodude/air-cookie)
![Tested_up_to WordPress_5.8](https://img.shields.io/badge/Tested_up_to-WordPress_5.8-blue.svg?style=flat-square)
![Compatible_with PHP_7.2](https://img.shields.io/badge/Compatible_with-PHP_7.2-green.svg?style=flat-square)
[![Build Status](https://img.shields.io/travis/com/digitoimistodude/air-cookie.svg?style=flat-square)](https://travis-ci.com/digitoimistodude/air-cookie)
[![CookieConsent_Version](https://img.shields.io/badge/CookieConsent%20Version-2.5.0-yellow?style=flat-square)](https://github.com/orestbida/cookieconsent)

Air cookie provides simple cookie banner and management.

Uses the [CookieConsent](https://orestbida.com/demo-projects/cookieconsent/) javascript plugin as a base, making usage with WordPress easier.

Current implemented version of CookieConsent: 2.5.0

## Please note before using

Air cookie is developed by [Digitoimisto Dude](https://dude.fi) and can update based on our clients need. By using the plugin, you agree that anything can change without a prior warning.

## Features

- Simple and lightweight cookie banner
- Support for multiple different cookie categories
- Easy to load scripts and execute custom javascript when cookies are accepted
- Polylang support for multilingual websites
- Visitor consent recording
- Cookie categories revision control

## Usage

### Cookie categories

By default, plugin has two cookie categories `necessary` and `analytics`. You may add new categories with `air_cookie\categories` filter like shown below.

```php
add_filter( 'air_cookie\categories', 'my_add_cookie_category' );
function my_add_cookie_category( $categories ) {
  $categories[] = [
    'key'         => 'ads',
    'enabled'     => false, // it is advised to have categories disabled by default
    'readonly'    => false, // user should have always control over categories
    'title'       => 'Ads',
    'description' => 'This site uses external services to display ads, and they might set some cookies.',
  ];

  return $categories;
}
```

When adding new categories, the function itself responsile for handling the translations for title and description.

There is also `air_cookie\categories\{category-key}` filter available to change the settings of indivual categories.

### Loading scripts after cookies have been accepted

The easiest way to load external script is by altering the `script` tag.

```html
<script type="text/plain" data-src="<uri-to-script>" data-cookiecategory="analytics" defer>
```

The example above works only, if the script does not require any extra javascript to be executed after the script has been loaded. If you need to execute extra javascript, use example below.

```php
add_action( 'air_cookie_js_analytics', 'my_add_js_for_analytics' );
function my_add_js_for_analytics() {
  ob_start(); ?>
    cc.loadScript( 'https://www.google-analytics.com/analytics.js', function() {
      ga('create', 'UA-XXXXXXXX-Y', 'auto');  //replace UA-XXXXXXXX-Y with your tracking code
      ga('send', 'pageview');
    } );
  <?php echo ob_get_clean();
}
```

### Executing custom javascript after cookies have been accepted

You may execute your own javascript after certain cookie categories have been accepted. There are two ways of doing that: adding the javascript inline to header or using custom javascript events.

#### Adding the javascript inline

Each cookie category do get its own action, from which javascript can be outputted to be added in the main header script tag.

```php
add_action( 'air_cookie_js_<category-key>', 'my_add_js_for_<category-key>' );
function my_add_js_for_<category-key>() {
  ob_start(); ?>
    console.log( 'Hello world!' );
  <?php echo ob_get_clean();
}
```

#### In separate javascript file

If you have custom javascript files, in which you need to exceute code only after certain categories are accepted, there is custom javascript events available.

Each cookie category do get its own event, to which you can add event listener into.

```javascript
document.addEventListener( 'air_cookie_<category-key>', (event) => {
  console.log( 'Hello world!' );
} );
```

There is also a global custom event available, in which the category key is passed as an detail.

```javascript
document.addEventListener( 'air_cookie', (event) => {
  console.log( 'Hello world! The category is ' + event.detail.category );
} );
```

### Chaning settings

Setting names do follow the CookieConsents option names. Find all available options from [CookieConsents readme](https://github.com/orestbida/cookieconsent#apis--configuration-parameters).

Some settings are set to be different than the CookieConsent defaults:

Setting | Value
--- | ---
cookie_name | air_cookie
current_lang | <value-from-polylang-or-locale-option>
revision | <automatically-calculated-from-cookie-categories>
page_scripts | true
gui_options/consent_modal/layout | box
gui_options/consent_modal/position | bottom left

You may change the settings with `air_cookie\settings` filter which contains all settings or `air_cookie\settings\{setting-name}` filter for indivual setting.

```php
add_filter( 'air_cookie\settings', 'my_modify_cc_settings' );
function my_modify_cc_settings( $settings ) {
  $settings['page_scripts'] = false;
  return $settings;
}
```

```php
add_filter( 'air_cookie\settings\page_scripts', 'my_modify_cc_setting_page_scripts' );
function my_modify_cc_setting_page_scripts( $setting ) {
  return false;
}
```

Note that these filters *do not yet* contain the language object or cookie categories. The `air_cookie\settings_all` filter contains everyhing, but usage is highly disencouraged as everything do have their own filters.

### Modifying default strings

If there is a need to modify default strings, the most preferred way to chage those is via Polylang string translations.

In cases of Polylang not being used, there is `air_cookie\strings` filter which contains all strings and `air_cookie\strings\{string-key}` filters for indivual strings.

```php
add_filter( 'air_cookie\strings', 'my_modify_cc_strings' );
function my_modify_cc_strings( $strings ) {
  $strings['consent_modal_title'] = 'Have a cookie?';
  return $strings;
}
```

```php
add_filter( 'air_cookie\strings\consent_modal_title', 'my_modify_cc_string_consent_modal_title' );
function my_modify_cc_string_consent_modal_title( $string ) {
  return 'Have a cookie?';
}
```

### Revision control

Cookie policy revision number is automatically calculated from cookie categories `key`, `enabled` and `readonly` values. If new categories are added, some are removed or the values changed the user will be prompted with consent modal.

In case you wish to have manual control over revisions, use `air_cookie\categories\version` filter. First argument is the calculated revision number and second is array containing all current cookie categories.

### Visitor consent recording

Finnish cookie law requires the site owners to be able to point when certain visitor has accepted the cookies. This is why the plugin has simple visitor consent recording system.

Each visitor is given unique uuid4 ID stored in separate cookie. When visitor accepts any cookie categories, their browser will send a small request to REST API which records their ID, current revision, accepted cookie categories, timestamp of the event and timestamp when the cookie expires. Each time visitor accepts or rejects new cookie category, new consent is recorded.

This data is stored in custom databse table and does not contain any extra information about the visitor.

You may change the visitor identification cookie name with `air_cookie\identification_cookie\name` filter and the expiration with `air_cookie\identification_cookie\name` filter.

Currently there is no way to disable this feature.

## Installing

Download [latest](https://github.com/digitoimistodude/air-cookie/releases/latest) version as a zip package and unzip it to your plugins directiry.

Or install with composer, running command `composer require digitoimistodude/air-cookie` in your project directory or add `"digitoimistodude/air-cookie":"dev-master"` to your composer.json require.

### Updates

Updates will be automatically distributed when new version is released.

## Changelog

Changelog can be found from [releases page](https://github.com/digitoimistodude/air-cookie/releases).

## Contributing

If you have ideas about the plugin or spot an issue, please let us know. Before contributing ideas or reporting an issue about "missing" features or things regarding to the nature of that matter, please read [Please note](#please-note-before-using) section. Thank you very much.
