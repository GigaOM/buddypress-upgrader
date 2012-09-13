# BuddyPress Upgrader

* Contributors: [borkweb](http://github.com/borkweb) of [GigaOM](http://gigaom.com)
* Tags: buddypress
* Requires at least: 3.4.1, BuddyPress 1.6.1
* Tested up to: 3.4.1
* Stable tag: 1.0
* License: GPLv2 or later
* License URI: http://www.gnu.org/licenses/gpl-2.0.html

Upgrade BuddyPress from version 1.0.3 (or later) to the latest release.

## Description

Have a super old version of BuddyPress and you don't want to install each major BuddyPress milestone version and run through the upgrade process
for each?  This plugin _should_ bring your installation up to snuff.  

Found a bug or something we missed? Check out the code and contribute [on github](https://github.com/GigaOM/buddypress-uprader). Pull requests are more than welcome!

## Installation

_Note: Run this before you run the BuddyPress "update wizard"._

1. **WARNING: Back Up your database.  This plugin WILL drop database columns and tables.  Just because it worked as is for one outdated installation.  Doesn't mean it will work everywhere.**
2. Upload the `buddypress-upgrader` folder to your plugins directory (e.g. `/wp-content/plugins/`)
3. Activate the plugin through the 'Plugins' menu in WordPress.
4. Find the _BP Magical Upgrader_ menu in the sidebar and click it.
5. Click _Upgrade_!
6. You are done.  Disable the plugin and run through the BuddyPress _update wizard_.

## Changelog

### 1.0
* Intitial release
