=== Groups for Divi Conditions ===
Contributors: abundantdesigns
Tags: divi, groups, conditions
Requires at least: 6.9
Requires PHP: 7.4
Stable tag: 1.0.1
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Adds a Groups Membership display condition to Divi 5.

== Description ==

Groups for Divi Conditions adds a Groups Membership condition to Divi 5's Advanced → Conditions panel.

Choose "Is a member of" or "Is not a member of", then select one or more Groups. The module is shown when the current visitor belongs to any selected group (or, with "Is not a member of", when they belong to none of them). Membership is the direct Groups membership check. Group IDs are stored on the module, so renaming a group does not break saved layouts.

The condition is evaluated by Divi 5 when it renders a native module. Shortcode modules have no render callback, so this plugin hides that block when the condition fails. It does not wrap modules or read Divi 4 shortcodes.

== Installation ==

1. Install and activate Groups and Divi 5.
2. Upload this plugin to `/wp-content/plugins/groups-divi-conditions/` and activate it.
3. Edit a module in the Divi 5 Visual Builder. Open Advanced → Conditions and add Groups Membership.

== Changelog ==

= 1.0.1 =
* Hide Divi shortcode modules when their Groups Membership condition fails. Divi only applies conditions to modules that have a render callback.

= 1.0.0 =
* Initial release.
