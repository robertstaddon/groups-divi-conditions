# Groups for Divi Conditions

A Groups membership condition for the Divi 5 Visual Builder.

Open any module, go to **Advanced → Conditions**, and add **Groups Membership**. The module is shown or hidden from the current visitor based on their [Groups](https://wordpress.org/plugins/groups/) membership. Divi evaluates the condition while it renders a native module. Shortcode modules have no render callback, so this plugin hides that block when the condition fails. It does not wrap modules and does not read Divi 4 shortcodes.

## Requirements

- WordPress 6.9 or newer
- PHP 7.4 or newer
- Divi 5
- Groups

If Divi 5 or Groups is inactive, administrators see a notice. Member-only modules stay hidden until Groups is available again.

## Usage

1. Activate Groups, Divi 5, and this plugin.
2. Edit a module in the Divi 5 Visual Builder.
3. Open **Advanced → Conditions** and add **Groups Membership**.
4. Choose a display rule:
   - **Is a member of** shows the module when the visitor belongs to any selected group.
   - **Is not a member of** shows the module when the visitor belongs to none of the selected groups.
5. Select one or more groups.

Selecting several groups means **any** of them. To require every group, add a separate Groups Membership condition for each group and set Divi's condition operator to **Display if all conditions are true**.

The condition stores group IDs. Renaming a group does not change existing layouts. A deleted group is ignored. With no groups selected, **Is a member of** hides the module and **Is not a member of** shows it. Guests are not members.

## Install

Copy `groups-divi-conditions` into `wp-content/plugins/` and activate it from the Plugins screen. Updates are delivered from the Abundant Designs update server.

## License

[GPL-2.0-or-later](https://www.gnu.org/licenses/gpl-2.0.html)
