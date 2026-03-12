=== Exotic Chat Landing ===
Contributors: exotic-online
Tags: chat, support board, route, landing, languages
Requires at least: 6.0
Tested up to: 6.9
Requires PHP: 8.0
Stable tag: 1.2.1
License: GPLv2 or later

Plug-and-play modern /chat landing route for Support Board Cloud.

== Description ==

Exotic Chat Landing adds a dedicated chat route (default: `/chat`) with modern UX and Support Board Cloud integration.

Features:

- Route activation on plugin activate
- Plug-and-play country auto-detection from hostname (e.g. `exotictanzania.com` -> `TZ`)
- Automatic department fallback by country for Exotic markets (Kenya=1 ... Egypt=22)
- Market-specific language profiles with conservative defaults
- Initial packaged landing languages: English, Kiswahili, French
- Support Board Cloud `lang=` integration on the landing route only
- Separate landing-shell language and widget language controls per market
- Mobile-safe auto-open and focus fallback UX
- Chat ID resolution order:
  1. `support-board-cloud` option (`sbcloud-settings.chat-id`)
  2. Plugin fallback chat ID setting
  3. Hardcoded fallback (`1369683147`)
- Country/domain-based department mapping
- Optional signed `dept` override (`dept`, `exp`, `sig`)
- Core visitor metadata capture, including landing language
- No changes required in theme files

== Installation ==

1. Upload `exotic-chat-landing` to `/wp-content/plugins/`
2. Activate the plugin in WordPress admin
3. Go to `Exotic Chat > Settings` (or `Settings > Exotic Chat Landing`)
4. Confirm the country routing and market language profile
5. Visit `/chat`

If the settings menu is not visible, open directly:
`/wp-admin/admin.php?page=exotic-chat-landing`

== Settings ==

General route settings:

- `enabled`: enable/disable route handling
- `route_slug`: route path (default `chat`)
- `fallback_chat_id`: used when Support Board Cloud setting is not available
- `default_department_id`: fallback department ID
- `country_map_json`: hostname map
- `allow_signed_override`: enable secure query-based department override
- `auto_open_delay_ms`: delay before opening chat (default 1500)

Country language profiles:

- `default_language`: the landing language that market uses by default
- `enabled_languages`: which packaged/admin-translated languages the market can use
- `allow_query_override`: allow `?lang=fr` style links for that market
- `use_wp_language`: let WordPress/WPML/Polylang language choose the landing language
- `use_browser_language`: let the visitor browser language choose the landing language
- `widget_language`: choose which Support Board UI language should be used for each landing language in that market
- `translations`: admin overrides for landing copy, stored per market and per language

Language resolution order:

1. `?lang=<code>` if enabled for that market
2. WordPress language if enabled for that market
3. Browser language if enabled for that market
4. Market default language
5. English fallback

Important: language switching is opt-in. A market will stay on its configured default language until you explicitly enable another resolution method.

Important: landing-shell language and Support Board widget language are independent. Example: Tanzania can show a Kiswahili landing shell while the embedded widget remains in English.

Example `country_map_json`:

{
  "exotickenya.com": {"country_code": "KE", "department_id": 1},
  "www.exotickenya.com": {"country_code": "KE", "department_id": 1}
}

Important: Department IDs are account-specific. Verify IDs from Support Board `Settings > Miscellaneous > Departments` before setting mappings.

Zero-config default department map bundled in v1.2.0:

`KE=1, GH=2, ZA=3, NG=4, TZ=5, CI=6, SN=7, ET=8, ZM=9, BJ=10, TG=11, SS=12, UG=13, RW=14, CD=15, AO=16, MZ=17, BW=18, NA=19, MW=20, ZW=21, EG=22`

== Kenya-First Rollout Checklist ==

1. Confirm Support Board Cloud chat ID is configured in `support-board-cloud`
2. Set Kenya domain mapping in `country_map_json`
3. Confirm Kenya department ID is `1`
4. In Kenya language profile, keep default language as `English`
5. Keep Kenya widget language mapped to `English`
6. Enable Kiswahili only if/when Kenya admins want it available
7. Leave WordPress/browser language toggles off unless Kenya explicitly wants automatic switching
8. Activate plugin and flush permalinks if needed
9. Test `https://exotickenya.com/chat`

== Multi-Country Extension ==

To roll out to another country site:

1. Install and activate the same plugin package
2. Confirm that site hostname maps to the correct country and department
3. Open the country language profile for that market
4. Set the market default language
5. Enable only the languages that market should use
6. For each enabled landing language, choose the matching widget language
7. Enable query/WP/browser language resolution only if that market explicitly wants it
8. Repeat `/chat` smoke test

Tanzania default bootstrap in v1.2.1:

- landing default: English
- enabled landing languages: English, Kiswahili
- widget language map: English -> English, Kiswahili -> English

== Signed Override ==

Enable `allow_signed_override` and define a secret constant in WordPress config:

`define('EXOTIC_CHAT_ROUTE_SECRET', 'replace-with-strong-secret');`

Link format:

`/chat?dept=<department_id>&exp=<unix_timestamp>&sig=<sha256_hmac>`

Signature payload format:

`<dept>|<exp>|<host>`

== Changelog ==

= 1.2.1 =
- Add separate per-market widget language mapping for each landing language
- Seed Tanzania with Kiswahili landing shell support and English widget fallback
- Keep Kenya bootstrap default conservative with English-only landing/widget

= 1.2.0 =
- Add market-specific multilingual landing profiles
- Add explicit `?lang=` support with per-market enablement
- Add WordPress and browser language resolution toggles per market
- Add packaged English, Kiswahili, and French landing strings
- Pass resolved landing language to Support Board Cloud via `lang=`
- Attach landing language to chat metadata

= 1.1.0 =
- Add top-level admin menu (`Exotic Chat`) and direct settings access
- Add plugin action `Settings` link from Plugins screen
- Add automatic host-to-country and country-to-department fallback routing
- Add activation/bootstrap defaults for current site host

= 1.0.0 =
- Initial release
