[![Software License](https://img.shields.io/badge/License-GPL%20v2-green.svg?style=flat-square)](LICENSE) [![PHP 7.2\+](https://img.shields.io/badge/PHP-7.2-blue?style=flat-square)](https://php.net) [![PHP 7.4\+](https://img.shields.io/badge/PHP-7.4-blue?style=flat-square)](https://php.net) [![WordPress 5](https://img.shields.io/badge/WordPress-5.8-orange?style=flat-square)](https://wordpress.org)

# EasyMap

Uncomplicated map functionality for WordPress.

## Description

The EasyMap for WordPress plugin will let you put Google Maps on your WordPress website with very little effort. The bottom line idea is "simple". You can have one (1) map per content page, you can have up to 200 pins/markers on a given map. You may specify which pins/markers should appear on a given map. You cannot customize pin colors. You can, however, customize just about every display aspect of the output using CSS.

The EasyMap plugin requires a Google API key suitable for Google Maps and Google Geocoding. This is a requirement imposed by Google.

In its first release, the EasyMap plugin supports only Google Maps.

The WordPress slug is `easymap`.

The plugin is also available on [wordpress.org](https://wordpress.org/plugins/easymap/)

### Basic functionality includes:

* Up to 200 locations can be displayed
* Choose to display all or some of the configured locations
* Choose Google Maps details to be shown
* Support for custom CSS
* Export and Import locations and plugin configuration
* Support for multiple address formats

### Other notes:

* This plugin `may` work with earlier versions of WordPress
* This plugin has been tested with `WordPress 5.4, 5.5, 5.6, 5.7, and 5.8` at the time of this writing
* This plugin optionally makes use of `mb_` PHP functions
* This plugin may create entries in your PHP error log (if active)
* This plugin contains no tracking code and does not store any information about users

## Installation

This section describes how to install the plugin and get it working.

1. Upload the contents of the `easymap` folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Configure the basic settings
4. To enable Google Maps integration, you will need a Google Maps API key with geolocation activated

## Frequently Asked Questions

### Is the plugin locale aware

EasyMap uses standard WordPress functionality to handle localization/locale. The native language localization of the plugin is English. It has been translated to Swedish by the author.

### Are there any incompatibilities

This is a hard question to answer. There are no known incompatibilities.

## Changelog

### 1.0.1
* Correction of activation issue
* Better handling of HTML in templates and description fields

### 1.0.0
* Initial release

## Upgrade Notice

### 1.0.1
* Simply update the plugin from the WordPress plugins

### 1.0.0
* Initial release

## License

Please see [LICENSE](LICENSE) for a full copy of GPLv2

Copyright (C) 2021 [Joaquim Homrighausen](https://github.com/joho1968); all rights reserved.

The EasyMap Plugin was written by Joaquim Homrighausen while converting :coffee: into code.

EasyMap is sponsored by [WebbPlatsen i Sverige AB](https://webbplatsen.se), Stockholm, Sweden.

Commercial support and customizations for this plugin is available from WebbPlatsen i Sverige AB in Stockholm, :sweden:

If you find this plugin useful, the author is happy to receive a donation, good review, or just a kind word.

If there is something you feel to be missing from this plugin, or if you have found a problem with the code or a feature, please do not hesitate to reach out to support@webbplatsen.se.

This plugin can also be downloaded from [code.webbplatsen.net](https://code.webbplatsen.net/wordpress/easymap/) and [GitHub](https://github.com/joho1968/easymap)

More detailed documentation is available at [code.webbplatsen.net/documentation/easymap/](https://code.webbplatsen.net/documentation/easymap/)

Icon and banner images based on [freesvg.org/map-location-icon-vector-image](https://freesvg.org/map-location-icon-vector-image). Kudos!

### External references

These links are not here for any sort of endorsement or marketing, they're purely for informational purposes.

* me; :monkey: https://joho.se and https://github.com/joho1968
* WebbPlatsen; https://webbplatsen.se and https://code.webbplatsen.net
