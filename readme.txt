=== Menu Override ===
Contributors: fillup17
Tags: menu override, custom menu, custom navigation
Requires at least: 3.5.1
Tested up to: 3.9
Stable tag: trunk
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Override the menu in use on a page level when your template only supports one.

== Description ==

On medium to large sites there is often a need to have a "global navigation" menu that exists at the top of every page of the site and then have section level navigation menus on various pages of the site. Not many themes allow you to do this so I created this plugin to fix that.

== Installation ==

Simply install the plugin through the Wordpress plugin manager. If you are not able to do so automatically, you an also download the plugin and put it in the wp-contet/plugins folder.

After the plugin is installed you'll have a new option when editing pages in the right column to select which menu should be displayed on the page.

== Changelog ==

= 0.4.1 =
* Bug fixes from @cfenzo to fix issues using global $post and enabling support for overriding menu on "page_for_posts" page.

= 0.3 =
* Added support to override menus on individual post pages

= 0.2.1 =
* Minor fix to prevent php warning from displaying

= 0.2 =
* Added ability to override multiple menu locations on a single page

= 0.1 =
* Initial Release

== Upgrade Notice ==

= 0.4.1 =
* Upgrade is safe from 0.2.x and 0.3.x. 

= 0.3 =
* Upgrade is safe from 0.2.x, data structure does not change so current overrides will not be lost.

= 0.2 =
* When upgrading, due to data structure changes, your current overrides will be lost, sorry.

= 0.1 =
* Initial Release