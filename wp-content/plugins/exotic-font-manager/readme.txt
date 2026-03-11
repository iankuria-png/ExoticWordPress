=== Exotic Font Manager ===
Contributors: exotic-online
Tags: fonts, typography, branding, custom fonts
Requires at least: 6.0
Tested up to: 6.9
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

A non-technical WordPress font manager for whole-site, page-level, and section-level typography rules.

== Description ==

Exotic Font Manager provides a guided admin UI so teams can safely manage typography without editing theme files.

Features:

* Guided typography presets for key site sections
* Scope controls: site-wide, front page, post type, page ID, post ID, taxonomy
* Font library with local uploads and CDN sources
* Google Fonts integration in two modes:
** Install locally (downloads font files into uploads)
** CDN mode (Google stylesheet)
* Custom CDN stylesheet support for third-party font providers
* Rule-level delivery mode: Auto / Local / CDN
* Safety controls: rollback snapshots and reset rules
* Cross-site profile export/import (JSON)

== Installation ==

1. Upload the `exotic-font-manager` folder to `/wp-content/plugins/`.
2. Activate the plugin through the Plugins screen.
3. Open `Font Manager` from the admin menu.
4. Add fonts to the library and create rules.

== Frequently Asked Questions ==

= Will this edit my theme files? =
No. The plugin applies generated CSS at runtime.

= Can I use this across multiple independent WordPress sites? =
Yes. Export a profile from one site and import it into another.

= Can I use CDN fonts and local fonts together? =
Yes. Each rule can choose Auto, Local, or CDN delivery.

== Changelog ==

= 1.0.0 =
* Initial release.
