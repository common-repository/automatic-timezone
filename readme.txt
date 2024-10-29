=== Automatic Timezone ===
Contributors: Otto
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=otto%40ottodestruct%2ecom
Tags: dst, daylight, savings, time, timezone, offset, UTC, GMT, otto, otto42
Requires at least: 2.5
Tested up to: 2.7.1
Stable tag: 1.7.1

Automatically sets the offset based on an input timezone. Automatically adjusts for daylight savings time, if necessary.

== Description ==

WordPress normally requires you to set a date offset for the blog in terms of a numeric difference from GMT/UTC. This plugin uses the <a href="http://en.wikipedia.org/wiki/Zoneinfo">zoneinfo</a> database, built into PHP 5 and most Linux systems, to allow you to select a timezone instead. The advantage of this is that the database contains all that is needed for daylight savings time adjustments, and you don't have to manually change the clock twice a year anymore.

THIS PLUGIN IS NO LONGER NECESSARY.

WordPress 2.8 now includes the functionality from this plugin in the core code. Only versions of WordPress prior to 2.8 will derive any benefit from this plugin.

If you are using WordPress 2.8 or up, you can delete this plugin entirely.


== Installation ==

1. Upload `timezone.php` to the `/wp-content/plugins/automatic-timezone/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Pick your timezone on the Plugins -> Timezone Configuration screen.

== Frequently Asked Questions ==

= What are these cities? =

The zoneinfo database uses a method of timezone choices that most people may not be familiar with. Instead of picking a named timezone, you pick a major city close to you that shares your timezone. The reason for this is although there's 24 hours in the day, there's hundreds of different ways of dealing with daylight savings time around the world, and laws and such change these all the time. The zoneinfo database tracks these and stores each different set of settings in a different "named" timezone, named after an area, such as a city, that uses that set of rules. So instead of picking something like "Central Time" or "Greenwich Mean Time", you will choose a city. That determines what set of daylight savings rules you will use. For your convienence, the cities are grouped by continent.

= The screenshot shows more information than my screen does! =

If you are using PHP 5.2 and up, you will get some extra information such as the time of the next daylight savings switch. This code does not work in versions of PHP previous to 5.2. If your host supports it, please consider switching your hosting service to use PHP 5 and up. Many hosts provide both PHP 4 and 5, but have PHP 4 as the default. PHP 5 is much better and has much better support for this sort of thing. More and more systems are <a href="http://gophp5.org/">requiring PHP 5</a> nowadays, and WordPress might be next. Do yourself a favor and ask your hosting service how you can use PHP 5 now. Beat the rush.

= The plugin won't activate, it says it can't find a list of timezones. =

The plugin won't work on some systems. Notably, Windows servers running PHP 4. Upgrade to PHP 5.

= It's not working, and I'm using PHP 4 on a non-Windows system. =

This plugin works best with PHP 5.1 and up, but it should also work okay on some PHP 4 systems with Linux/Unix hosting. If it doesn't, email me and I'll try to help. It does try a few different methods, but if it won't activate, then it also won't work, so don't try to force it or anything.

== Screenshots ==

1. The configuration screen.
1. The list of cities on the configuration screen.
1. The Timezone setting in Settings->General. This message will only change when it is working and you have selected a timezone.


== Changelog ==

= 1.7.1 =
* Fixed translation functionality for special activation case. Never assume a global when you're unsure. :)

= 1.7 =
* Add an icon for the menu item for 2.7 and up installations.
* Add uninstall functionality for 2.7 and up installations.

= 1.6 =
* Added link to Settings page from plugin display page. Looks nice and clean in 2.7. It would be nice if all plugins did this, could get rid of a lot of settings pages.

= 1.5 =
* Added two suggested features:
* Display of the current time and adjusted time on the config screen.
* Comment at the end explaining the lack of closing PHP tags (since some people think that is an error, when it's not)

= 1.4 =
* Got a report of a PHP 5.1.4 install which lacked the DateTimeZone class. Modified the code to check for that and to try to use the PHP 4 methodology in that case.

= 1.3 =
* Okay, so now internationalization support should *actually* be working. :)

= 1.2 =
* Add internationalization support. First stab at this sort of thing, let me know if I got anything wrong.

= 1.1 =
* Moved configuration page to Options menu, for MU compatibility. I still think that it should be in the Plugins menu, but if that's whatit takes to be compatible, then so be it. MU should be fixed in this respect.
* Added lots of comments in the code.
* Minor cleanups.

= 1.0 =
* Initial Release

