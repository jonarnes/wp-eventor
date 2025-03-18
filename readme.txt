=== Eventor Integration ===
Contributors: jonarnes
Tags: orienteering, eventor, events, sports
Requires at least: 5.0
Tested up to: 6.4
Stable tag: 1.0.0
Requires PHP: 7.4
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Display orienteering events from Eventor on your WordPress site.

== Description ==

Eventor Integration allows you to display events from Eventor (Norwegian Orienteering Federation's event system) on your WordPress site. Features include:

* Display upcoming and past events
* Show event details including location, date, and organizer
* Map integration with Google Maps
* Configurable date ranges
* Support for multiple organizations
* Event messages and registration deadlines
* Gutenberg block and shortcode support

= Usage =

Use either the Gutenberg block "Eventor Events" or the shortcode:
`[eventor_events]`

```
[eventor_events organisation_ids="13" days_back="60" layout="rich" past_events_count="1" days_forward="90"]
```

To display a card with data about a single event, use `[eventor_event event_id="123"]`

= Requirements =

* WordPress 5.0 or higher
* PHP 7.4 or higher
* Valid Eventor API key
* Organization ID from Eventor

== Installation ==

1. Upload the plugin files to `/wp-content/plugins/eventor-integration`
2. Activate the plugin through the 'Plugins' screen in WordPress
3. Go to Settings > Eventor Integration to configure your API key and settings

== Frequently Asked Questions ==

= Where do I get an API key? =

The API key is available in Eventor if you have the sufficient permissions. If not, contact someone in your organization who has the permissions.

= How do I find my organization ID? =

Your organization ID can be found in Eventor when logged in as an administrator. You can also figure out the ID by looking at the URL when viewing the organization in Eventor. The ID is the numbers at the end of the URL.

= Can I display events from multiple organizations? =

Yes, you can enter multiple organization IDs separated by commas in the settings.

== Screenshots ==

1. Events list display
2. Admin settings page
3. Gutenberg block

== Changelog ==

= 1.0.6 =
* Single event card layout change

= 1.0.5 =
* Added single event view using short code

= 1.0.4 =
* short code now supports params like the block editor

= 1.0.3 =
* bump version

= 1.0.2 =
* Layout can now be set to "dense" or "rich"

= 1.0.1 =
* Events list with past and upcoming events
* Map integration
* Configurable settings
* Gutenberg block support

== Upgrade Notice ==

= 1.0.0 =
Initial release

== Privacy Policy ==

This plugin connects to Eventor (eventor.orientering.no) to fetch event data. No personal data is collected or stored by the plugin itself.