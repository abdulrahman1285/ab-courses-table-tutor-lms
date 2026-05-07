=== AB Courses Table for Tutor LMS ===
Contributors: abdulrahmanbarakat
Tags: tutor lms, courses, table, arabic, rtl
Requires at least: 6.0
Tested up to: 6.9
Stable tag: 5.2.3
Requires PHP: 7.4
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Displays Tutor LMS Pro courses in a filterable RTL table with category tabs, online/offline toggle, search, and pagination.

== Description ==

AB Courses Table for Tutor LMS displays Tutor LMS Pro courses in a fully RTL-compatible filterable table. Works with any translation plugin including WPML.

**Features:**

* Dynamic category filter tabs from Tutor LMS taxonomy
* Online / Offline delivery mode toggle
* Live search by course name
* Pagination with configurable rows per page
* Optional columns: price, enrolled students, available seats, level, duration
* "Course full" badge with custom label
* Configurable primary and accent colors
* Transient cache for faster page loads
* Configurable sort order (publish date, name, start date)
* All Tutor LMS meta keys configurable from the admin settings page
* Full i18n support — compatible with WPML, Polylang, and any gettext-based translation plugin
* Includes Arabic translation out of the box

**Shortcode:**

`[abct_courses_table]`

== Installation ==

1. Upload the `ab-courses-table-tutor-lms` folder to `/wp-content/plugins/`
2. Activate the plugin from **Plugins > Installed Plugins**
3. Make sure **Tutor LMS Pro** is installed and active
4. Go to **Settings > Courses Table** to configure
5. Add the shortcode `[abct_courses_table]` to any page or post

== Frequently Asked Questions ==

= Where are the settings? =
**WordPress Admin > Settings > Courses Table**

= What shortcode do I use? =
`[abct_courses_table]`

= Does it work with WPML? =
Yes. The plugin includes a `wpml-config.xml` file for full WPML compatibility. All strings are translatable via WPML String Translation.

= The dates or times are not showing — why? =
Open the Settings page and update the Meta Field keys to match your Tutor LMS configuration. The defaults follow the standard Tutor LMS Pro meta key names.

= Does it work without Tutor LMS? =
No — the plugin requires Tutor LMS Pro to be installed and active.

= Will my settings be deleted if I uninstall the plugin? =
Yes. Uninstalling the plugin will remove all saved settings and cached data from your database.

== Screenshots ==

1. The courses table on the front-end with category tabs and mode toggle.
2. The admin settings page — Display Settings section.
3. The admin settings page — Tutor LMS meta key configuration.

== Changelog ==

= 5.2.0 =
* Fixed: Text domain updated to match plugin slug (ab-courses-table-for-tutor-lms)
* Fixed: Renamed language files to match new text domain
* Fixed: Escaped color values in wp_add_inline_style using sanitize_hex_color()

= 5.0.0 =
* Added: Full i18n support — all strings wrapped in __() / _e()
* Added: Arabic translation (.po and .mo files)
* Added: wpml-config.xml for WPML compatibility
* Added: uninstall.php to clean up settings on deletion
* Added: register_activation_hook to set default options
* Added: register_deactivation_hook to clear cache
* Fixed: Removed discouraged load_plugin_textdomain() call
* Fixed: Removed Author URI with discouraged domain

= 4.0.0 =
* Added: Seats, price, students, level, duration optional columns
* Added: Transient cache with configurable expiry
* Added: Sort by date, name, or start date
* Added: "Course full" badge with custom label
* Fixed: Replaced date() with gmdate() for timezone safety
* Fixed: Nonce verification on settings page
* Fixed: GPL license header

= 3.0.0 =
* Added: Admin settings page with color pickers
* Added: Category tabs and online/offline toggle

= 1.0.0 =
* Initial release

== Upgrade Notice ==

= 5.0.0 =
Added full translation support (WPML, Polylang). No breaking changes — safe to update.

