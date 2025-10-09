=== IAMMETER ===
Contributors: iammeter
Tags: energy meter, electricity monitoring, power consumption, smart meter, energy dashboard, iammeter, WEM3080, WEM3080T, WEM3046T, WEM3050T
Requires at least: 5.8
Tested up to: 6.8.1
Stable tag: 1.0.1
License: GPLv2 or later

Get meter data from IAMMETER Cloud and display it in WordPress posts. Supports single-phase and three-phase meters, configurable unlimited number of meter devices.

== Description ==

The IAMMETER WordPress plugin allows you to seamlessly integrate your energy meter data into your WordPress website. Connect to IAMMETER Cloud and display real-time electricity consumption, power generation, and energy statistics directly in your posts and pages.

This plugin supports both single-phase and three-phase energy meters, making it perfect for residential solar installations, commercial energy monitoring, and industrial power management systems.

Major features include:

* Easy integration with IAMMETER Cloud API
* Support for unlimited number of meter devices
* Real-time data display with automatic updates
* Responsive design that works on all devices
* Configurable data refresh intervals
* Support for both single-phase and three-phase meters
* Clean, modern card-based display layout
* Energy import and export monitoring
* Voltage, current, power, and power factor monitoring
* Debug mode for troubleshooting
* Secure API key management

Perfect for:
* Solar panel monitoring systems
* Home energy management
* Commercial building energy tracking
* Industrial power consumption analysis
* Energy efficiency reporting

The plugin uses shortcodes to display meter data anywhere on your website. Simply configure your IAMMETER API credentials and add the [iammeter] shortcode to any post or page.

== Installation ==

1. Upload the IAMMETER plugin files to your `/wp-content/plugins/iammeter` directory, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the 'Plugins' screen in WordPress.
3. Go to Settings > IAMMETER to configure the plugin.
4. Enter your IAMMETER API credentials (API key and meters configuration).
5. Add the [iammeter] shortcode to any post or page where you want to display meter data.

That's it! Your energy meter data will now be displayed on your website.

== Frequently Asked Questions ==

= How do I get IAMMETER API credentials? =

You need to have an IAMMETER account and energy meters connected to the IAMMETER Cloud service. Visit https://www.iammeter.com/ to learn more about IAMMETER products and services.

= Can I display multiple meters on the same page? =

Yes, the plugin supports unlimited number of meter devices. You can configure multiple meters in the plugin settings and they will all be displayed when you use the [iammeter] shortcode.

= What types of meters are supported? =

The plugin supports both single-phase and three-phase energy meters from IAMMETER. It automatically detects the meter type and displays the appropriate data format.

= How often is the data updated? =

The plugin uses a configurable cache system. You can set the data refresh interval in the plugin settings. The recommended minimum is 30 seconds to avoid excessive API calls.

= Is there a demo or test mode? =

Yes, the plugin includes a debug mode that provides detailed information about API calls and data processing. This is helpful for troubleshooting and setup verification.

== Changelog ==

= 1.0.1 =
*Release Date - 30 September 2025*

* Initial release of IAMMETER WordPress plugin
* Support for single-phase and three-phase energy meters
* Real-time data display with configurable refresh intervals
* Responsive card-based layout design
* Integration with IAMMETER Cloud API
* Shortcode support for easy content integration
* Debug mode for troubleshooting
* Secure API key management
* Support for unlimited number of meter devices
* Clean, modern user interface
* Energy import/export monitoring
* Comprehensive electrical parameter display (voltage, current, power, power factor)

== Upgrade Notice ==

= 1.0.1 =
This is the initial release of the IAMMETER WordPress plugin. Install to start monitoring your energy consumption directly on your WordPress website.
