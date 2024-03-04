# Air cookie

[![Packagist](https://img.shields.io/packagist/v/digitoimistodude/air-cookie.svg?style=flat-square)](https://packagist.org/packages/digitoimistodude/air-cookie)
[![CookieConsent_Version](https://img.shields.io/badge/CookieConsent%20Version-2.9.1-yellow?style=flat-square)](https://github.com/orestbida/cookieconsent)
![Tested_up_to WordPress_6.4.3](https://img.shields.io/badge/Tested_up_to-WordPress_6.4.3-blue.svg?style=flat-square)
![Compatible_with PHP_8.3](https://img.shields.io/badge/Compatible_with-PHP_8.3-green.svg?style=flat-square)
[![Build](https://github.com/digitoimistodude/air-cookie/actions/workflows/build.yml/badge.svg)](https://github.com/digitoimistodude/air-cookie/actions/workflows/build.yml)

Air cookie provides simple cookie banner and management.

Uses the [CookieConsent V3.0.0](https://playground.cookieconsent.orestbida.com/) javascript plugin as a base, making its usage with WordPress easier.

# Features

- Simple and lightweight cookie banner
- Third party embeds blocking until cookies accepted
- Allows to remove cookies after changing consent
- Easy to load scripts and execute custom javascript when cookies are accepted
- Support for multiple different cookie categories
- Polylang support for multilingual websites
- Visitor consent recording
- Cookie categories revision control

# Usage

## Link to cookie settings

Remember to add a link into the footer, which allows opening the cookie settings anytime!

```html
<button type="button" data-cc="show-preferencesModal">View cookie settings</button>
```

If you have Polylang installed and active, use

```html
<button type="button" data-cc="show-preferencesModal">
  <?php echo pll_translate_string( 'Evästeasetukset', pll_current_language() ); ?>
</button>
```

## Cookie categories

By default, plugin has two cookie categories `necessary`, `functional` and `analytics`. You may add new categories with `air_cookie\categories` filter like shown below.

```php
add_filter( 'air_cookie\categories', 'my_add_cookie_category' );
function my_add_cookie_category( $categories ) {
  $categories[] = [
    'key'         => 'ads',
    'enabled'     => false, // it is advised to have categories disabled by default
    'readonly'    => false, // user should have always control over categories
    'title'       => 'Ads',
    'description' => 'This site uses external services to display ads, and they might set some cookies.', // Check how to remove cookies from example below.
  ];

  return $categories;
}
```

When adding new categories, the function itself is responsile for handling the translations for title and description.

There is also `air_cookie\categories\{category-key}` filter available to change the settings of indivual category.
```php
add_filter( 'air_cookie\categories\{analytics}', 'my_change_category_analytics' );
function my_change_category_analytics( $edited_categoy ) {
  $edited_category = [
    'key'         => 'analytics',
    'enabled'     => false,
    'readonly'    => false,
    'title'       => 'Analytics',
    'description' => 'This site uses Google Analytics and it set some cookies. Read more about those from privacy policy.',
    'autoClear'   => [ // Autoclear allows you to define cookies, which will be removed after changing consent. Possible to use string or regex format (format is a bit different than official docs points out! https://cookieconsent.orestbida.com/reference/configuration-reference.html#category-autoclear).
      'cookies'     => [
          [
            'name'  => '^(_ga)', // Match all cookies starting with '_ga',
          ],
          [
            'name'  => '_gid',
          ],
        ],
    ],
  ];

  return $edited_category;
}

// With Google Analytics rememember consent mode: https://developers.google.com/tag-platform/security/guides/consent?consentmode=advanced#upgrade-consent-v2. Accept and remove consent need to be implemented directly in plugin. Use ccOnAccept() and ccOnChange(). Script-injection.php line 59 ->
```

## Loading scripts after cookies have been accepted

The easiest way to load external script is by altering the `script` tag to be:

```html
<script type="text/plain" data-src="<uri-to-script>" data-category="analytics">
```

The example above works only, if the script does not require any extra javascript to be executed after the script has been loaded. If you need to execute extra javascript, use the example below.

```php
add_action( 'air_cookie_js_analytics', 'my_add_js_for_analytics' );
function my_add_js_for_analytics() {
  ob_start(); ?>
    CookieConsent.loadScript( 'https://www.google-analytics.com/analytics.js', function() {
      ga('create', 'UA-XXXXXXXX-Y', 'auto');  //replace UA-XXXXXXXX-Y with your tracking code
      ga('send', 'pageview');
    } );
  <?php echo ob_get_clean();
}
```

### Google Tag Manager (gtm.js)

```php
add_action( 'air_cookie_js_analytics', 'my_add_js_for_analytics' );
function my_add_js_for_analytics() {
  ob_start(); ?>
    (function (w, d, s, l, i) {
      w[l] = w[l] || [];
      w[l].push({ "gtm.start": new Date().getTime(), event: "gtm.js" });
      var f = d.getElementsByTagName(s)[0],
        j = d.createElement(s),
        dl = l != "dataLayer" ? "&l=" + l : "";
      j.async = true;
      j.src = "https://www.googletagmanager.com/gtm.js?id=" + i + dl;
      f.parentNode.insertBefore(j, f);
    })(window, document, "script", "dataLayer", "GTM-XXXXXX");
  <?php echo ob_get_clean();
}
```

### Google Tag Manager (gtag.js)

```php
add_action( 'air_cookie_js_analytics', 'my_add_js_for_analytics' );
function my_add_js_for_analytics() {
  ob_start(); ?>
    CookieConsent.loadScript( 'https://www.googletagmanager.com/gtag/js?id=UA-XXXXXXXXX-X', function() {
      window.dataLayer = window.dataLayer || [];
      function gtag(){dataLayer.push(arguments);}
      gtag('js', new Date());
      gtag('config', 'UA-XXXXXXXXX-X');
    });
  <?php echo ob_get_clean();
}
```

### Google Analytics (analytics.js)

```php
add_action( 'air_cookie_js_analytics', 'my_add_js_for_analytics' );
function my_add_js_for_analytics() {
  ob_start(); ?>
    CookieConsent.loadScript( 'https://www.google-analytics.com/analytics.js', function() {
      ga('create', 'UA-XXXXXXXX-Y', 'auto');  //replace UA-XXXXXXXX-Y with your tracking code
      ga('send', 'pageview');
    } );
  <?php echo ob_get_clean();
}
```

## Moving plugin javascript to be loaded after cookies have been accepted

```php
// Disable script by nulling the tag on latest possible chance
add_filter( 'script_loader_tag', function( $tag, $handle ) {
  if ( 'our-script-handle' === $handle ) {
    return '';
  }

  return $tag;
}, 10, 2 );

// Load stamped.io script on air cookie
add_action( 'air_cookie_js_functional', function() {
  $stampedio_url = get_script_src_by_handle( 'our-script-handle' );
  if ( empty( $stampedio_url ) ) {
    return;
  }

  ob_start(); ?>
    CookieConsent.loadScript( '<?php echo esc_url( $stampedio_url ) ?>' );
  <?php echo ob_get_clean(); // phpcs:ignore
} ); // end woocommerce_ga_integration_script_for_air_cookie

function get_script_src_by_handle( $handle ) {
  global $wp_scripts;
  if ( in_array( $handle, $wp_scripts->queue ) ) {
    return $wp_scripts->registered[ $handle ]->src;
  }
} // end get_script_src_by_handle

// Disable stamped.io script by nulling the tag on latest possible chance
add_filter( 'script_loader_tag', function( $tag, $handle ) {
  if ( 'woo-stamped-io-public-custom' === $handle ) {
    return '';
  }

  return $tag;
}, 10, 2 );

// Load stamped.io script on air cookie
add_action( 'air_cookie_js_functional', __NAMESPACE__ . '\woocommerce_stampedio_script_for_air_cookie' );
function woocommerce_stampedio_script_for_air_cookie() {
  $stampedio_url = get_script_src_by_handle( 'woo-stamped-io-public-custom' );
  if ( empty( $stampedio_url ) ) {
    return;
  }

  ob_start(); ?>
    CookieConsent.loadScript( '<?php echo esc_url( $stampedio_url ) ?>' );
  <?php echo ob_get_clean(); // phpcs:ignore
} // end woocommerce_ga_integration_script_for_air_cookie
```

## Executing custom javascript after cookies have been accepted

You may execute your own javascript after certain cookie categories have been accepted. There are two ways of doing that: adding the javascript inline to header or using custom javascript events.

### Adding the javascript inline

Each cookie category do get its own action, from which javascript can be outputted to be added in the main header script tag.

```php
add_action( 'air_cookie_js_<category-key>', 'my_add_js_for_<category-key>' );
function my_add_js_for_<category-key>() {
  ob_start(); ?>
    console.log( 'Hello world!' );
  <?php echo ob_get_clean();
}
```

If you wish to use your own `script` tag, it is possible with example below

```javascript
<script type="text/plain" data-category="<category-key>">
  console.log( 'Hello world!' );
</script>
```

### In separate javascript file

If you have custom javascript files in which you need to exceute code only after certain categories are accepted, there is custom javascript events available.

Each cookie category do get its own event, to which you can bind event listener into.

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

## Changing settings

Setting names do follow the [CookieConsents option](https://cookieconsent.orestbida.com/reference/configuration-reference.html#configuration-reference) names. Some settings defaults are set to be different than the CookieConsent defaults:

Setting | Value
--- | ---
cookie/name | air_cookie
`revision` | _automatically calculated from cookie categories_
`current_lang` | _value from polylang or locale option_
guiOptions/consentModal/layout | cloud inline
guiOptions/consentModal/position | bottom center

You may change the settings with `air_cookie\settings` filter which contains all settings or `air_cookie\settings\{setting-name}` filter for indivual setting.

```php
add_filter( 'air_cookie\settings', 'my_modify_cc_settings' );
function my_modify_cc_settings( $settings ) {
  $settings['guiOptions']['consentModal']['position'] = "top right";
  return $settings;
}
```

```php
add_filter( 'air_cookie\settings\page_scripts', 'my_modify_cc_setting_page_scripts' );
function my_modify_cc_setting_page_scripts( $setting ) {
  return false;
}
```

Note that these filters *do not** contain the strings or cookie categories. The `air_cookie\settings_all` filter contains everyhing, but usage is highly disencouraged as everything has their own specific filter.

## Modifying default strings

If there is a need to modify default strings, the most preferred way to chage those is via Polylang string translations. To change the strings for all languages, it is not required to change the source.

In case of Polylang not being used, there is `air_cookie\strings` filter which contains all strings and `air_cookie\strings\{string-key}` filters for indivual strings.

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

## Third party embeds blocking

By default third party embeds blocking is active and all embeds for following services are blocked: Youtube, Vimeo, Instagram, Facebook, Twitter, Soundcloud, Spotify, Slideshare, WordPress.com Video, Embedly, Issuu, Imgur and TikTok.

Disable this feature with `air_cookie\embeds` filter by returning false.

```php
add_filter( 'air_cookie\embeds', '__return_false' );
```

Iframe embeds are replaced with placeholder, letting visitor know that the embed might use tracking cookies. They then have opportunity to allow all cookies or enable the singular embed once.

For script embeds, we just replace the `src` tag with `data-src` and add `data-cookiecategory` which makes those to work CookieConsents way of loading scripts.

### Cookie category for embeds

When the feature is enabled, a new cookie category is added. When this category is accepted, all embeds are loaded and shown.

Use strings filter `air_cookie\strings` to modify the texts for this category.

### Thumbnails for placeholders

Iframe embed placeholders do support thumbnails. Without a thumbnail, the iframe is replaced with black background box. Youtube and Vimeo embeds do get automatically the placeholder.

For other services, you may use `air_cookie\embeds\thumbnail` filter like shown below.

```php
add_filter( 'air_cookie\embeds\thumbnail', 'my_maybe_add_thumbnail', 10, 2 );
function my_maybe_add_thumbnail( $thumbnail, $src ) {
  // If thumbnail is already set, bail.
  if ( ! empty( $thumbnail ) ) {
    return $thumbnail;
  }

  // Do your magic to get the thumbnail

  return 'https://your.thumbna.il/location.jpg';
}
```

### Vimeo and Do Not Track

Vimeo embeds are treated a bit differently. For those, we don't add placeholder nor block them. Instead, we add Do Not Track parameter for the embed src which disabled all Vimeo tracking and statistics.

Disable this feature with `air_cookie\embeds\vimeo\add_dnt` filter, but notice that WordPress Core adds the dnt by default as well.

If you wish to show the placeholder even if the Vimeo embed has Do Not Track enabled, use `air_cookie\embeds\vimeo\skip_dnt` filter.

## Revision control

Cookie policy revision number is automatically calculated from cookie categories `key`, `enabled` and `readonly` values. If new categories are added, some are removed or the values changed the consent modal will be shown again.

In case you wish to have manual control over revision number, use `air_cookie\categories\revision` filter. First argument is the calculated revision number and second is array containing all current cookie categories.

## Visitor consent recording

Finnish cookie law requires the site owner to be able to point when certain visitor has accepted cookies. This is why the plugin has simple visitor consent recording system.

Each visitor is given unique uuid4 ID. When visitor accepts any cookie categories, their browser will send a small request to REST API which records their ID, current revision, accepted cookie categories, timestamp of the event and timestamp of expiry. This data is stored in custom database table and does not contain any extra information about the visitor.

Currently there is no way to disable this feature.

# Installing

Download [latest](https://github.com/digitoimistodude/air-cookie/releases/latest) version as a zip package and unzip it to your plugins directiry.

Or install with composer, running command `composer require digitoimistodude/air-cookie` in your project directory or add `"digitoimistodude/air-cookie":"dev-master"` to your composer.json require.

## Updates

Updates will be automatically distributed when new version is released.

# Changelog

Changelog can be found from [releases page](https://github.com/digitoimistodude/air-cookie/releases).

# Contributing

If you have ideas about the plugin or spot an issue, please let us know. Before contributing ideas or reporting an issue about "missing" features or things regarding to the nature of that matter, please note that this plugin is drafted to fullfill our customers need and might not move into the direction you'd hope. Thank you very much.
