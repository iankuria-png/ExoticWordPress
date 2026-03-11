=== Exotic Chat Landing ===
Contributors: exotic-online
Tags: chat, support board, route, landing
Requires at least: 6.0
Tested up to: 6.9
Requires PHP: 8.0
Stable tag: 1.1.0
License: GPLv2 or later

Plug-and-play modern /chat landing route for Support Board Cloud.

== Description ==

Exotic Chat Landing adds a dedicated chat route (default: `/chat`) with modern UX and Support Board Cloud integration.

Features:

- Route activation on plugin activate
- Plug-and-play country auto-detection from hostname (e.g. `exotictanzania.com` -> `TZ`)
- Automatic department fallback by country for Exotic markets (Kenya=1 ... Egypt=22)
- Mobile-safe auto-open and focus fallback UX
- Chat ID resolution order:
  1. `support-board-cloud` option (`sbcloud-settings.chat-id`)
  2. Plugin fallback chat ID setting
  3. Hardcoded fallback (`1369683147`)
- Country/domain-based department mapping
- Optional signed `dept` override (`dept`, `exp`, `sig`)
- Core visitor metadata capture
- No changes required in theme files

== Installation ==

1. Upload `exotic-chat-landing` to `/wp-content/plugins/`
2. Activate the plugin in WordPress admin
3. Go to `Exotic Chat > Settings` (or `Settings > Exotic Chat Landing`)
4. Configure department and country map
5. Visit `/chat`

If the settings menu is not visible, open directly:
`/wp-admin/admin.php?page=exotic-chat-landing`

== Settings ==

- `enabled`: enable/disable route handling
- `route_slug`: route path (default `chat`)
- `fallback_chat_id`: used when Support Board Cloud setting is not available
- `default_department_id`: fallback department ID
- `country_map_json`: hostname map
- `allow_signed_override`: enable secure query-based override
- `auto_open_delay_ms`: delay before opening chat (default 1500)

Example `country_map_json`:

{
  "exotickenya.com": {"country_code": "KE", "department_id": 1},
  "www.exotickenya.com": {"country_code": "KE", "department_id": 1}
}

Important: Department IDs are account-specific. Verify IDs from Support Board `Settings > Miscellaneous > Departments` before setting mappings.

Zero-config default department map bundled in v1.1.0:

`KE=1, GH=2, ZA=3, NG=4, TZ=5, CI=6, SN=7, ET=8, ZM=9, BJ=10, TG=11, SS=12, UG=13, RW=14, CD=15, AO=16, MZ=17, BW=18, NA=19, MW=20, ZW=21, EG=22`

== Kenya-First Rollout Checklist ==

1. Confirm Support Board Cloud chat ID is configured in `support-board-cloud`
2. Set Kenya domain mapping in `country_map_json`
3. Set Kenya default department ID
4. Activate plugin and flush permalinks if needed
5. Test `https://exotickenya.com/chat`
6. Confirm:
   - chat opens automatically
   - fallback CTA works on mobile
   - metadata appears in Support Board
   - existing homepage widget still works

== Multi-Country Extension ==

To roll out to another country site:

1. Install and activate the same plugin package
2. Update `country_map_json` hostnames and department IDs
3. Optionally set a site-specific `fallback_chat_id`
4. Repeat `/chat` smoke test

== Signed Override ==

Enable `allow_signed_override` and define a secret constant in WordPress config:

`define('EXOTIC_CHAT_ROUTE_SECRET', 'replace-with-strong-secret');`

Link format:

`/chat?dept=<department_id>&exp=<unix_timestamp>&sig=<sha256_hmac>`

Signature payload format:

`<dept>|<exp>|<host>`

== Changelog ==

= 1.0.0 =
- Initial release

= 1.1.0 =
- Add top-level admin menu (`Exotic Chat`) and direct settings access
- Add plugin action `Settings` link from Plugins screen
- Add automatic host-to-country and country-to-department fallback routing
- Add activation/bootstrap defaults for current site host



