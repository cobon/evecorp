=== Eve Corporation ===
Contributors: mitome
Donate link: http://fisr.dnsd.info/
Tags: eve, eve online, eve-online, eve-igb, eve corporation, eve corp
Requires at least: 3.3
Tested up to: 3.4.2
Stable tag: trunk
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

WordPress plug-in for secure and easy Eve Online player corporation web sites.

== Description ==

This should help setting up a WordPress site for Eve Online player corporations.

Primary goals are ease of installation and configuration, while maintaining a
high level of security and confidentiality.

Planned features:
 * Support for Eve Online in-game browser (IGB) functionality.
 * Registration an Login with Eve Online API keys
 * Identity verification trough wallet transaction.
 * No passwords. email-address or other personal information required.
 * WordPress roles and capabilities derived from Eve Online corporation roles.
 * Uses Eve Online character portraits instead of Gravatar.
 * Time-zone support for members.
 * Defaults to members-only for new blog posts and pages.
 * Short-codes for in-game items, characters, corporations, solar systems, etc.
 * Auto-generated user-list and user-pages/character-sheet.

More ideas:

 * WordPress theme with added functionality.
 * Support for alternate characters (alt's).
 * Character sheet with available API information:
  - Skills
  - Current skill-queue
  - Planned skills (Evemon upload/import)
 * bbPress Forum integration
 * ICS calendar export from Eve Online
 * Alliances?

== Installation ==

This section describes how to install the plugin and get it working.

1. Upload and extract `evecorp.zip` to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress

== Changelog ==

= 0.1 =
* In development. Not for public consumption.

== Upgrade Notice ==

= 0.1 =
In development. Not for public consumption.


== Acknowledgment ==
This software uses Pheal <http://github.com/ppetermann/pheal/> a PHP library for
accessing the EVE API by Peter Petermann (eve: Peter Powers).

This program connects to and exchanges data with API servers of Eve Online.
by using this program you agree with the website terms of service of Eve Online
published under <http://community.eveonline.com/pnp/termsofuse.asp>.

Eve Online is copyright (c) 1997-2013, CCP hf, Reykjavík, Iceland

EVE Online and the EVE logo are the registered trademarks of CCP hf. All rights
are reserved worldwide.
