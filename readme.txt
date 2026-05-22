=== Underway ===
Contributors: annezazu, kellychoffman
Tags: dashboard, drafts, writing, blogging, ai
Requires at least: 6.5
Tested up to: 6.9
Requires PHP: 8.1
Stable tag: 0.1.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

A bundle of dashboard widgets that help your writing get underway: surface forgotten drafts, capture ideas, schedule future drafts, and build writing habits.

== Description ==

Underway bundles four small dashboard widgets that, together, address the full lifecycle of pre-publish writing — from a fleeting idea to a published post. Each widget is independently useful; together they form a quiet, on-dashboard writing companion.

**Included widgets:**

* **Draft Sweeper** — Resurfaces abandoned drafts intelligently in the dashboard, scoring them by completeness, recency, and topical relevance.
* **Future Drafts** — Capture an experience now as a tiny draft; the widget brings it back on the date you choose.
* **Ideas Inbox** — A per-user ideas inbox. Drop ideas now; convert any to a draft with one click.
* **Habit Creator** — Spots recurring patterns in your archive and nudges you to write the next installment.

**Optional AI enhancements.** Draft Sweeper and Habit Creator can use the WordPress AI Client (WordPress 7.0+) for one-line draft summaries and starter questions, respectively. Both widgets work fully without AI — connect a provider only if you want the extras.

**One settings page, one onboarding flow.** Choose which widgets to enable during activation; flip them on/off anytime from Settings → Underway.

== Installation ==

1. Upload the plugin to the `/wp-content/plugins/` directory, or install through the Plugins screen in WordPress.
2. Activate the plugin. You'll be redirected to a short setup screen.
3. Choose which widgets to enable, then visit the Dashboard.
4. To change settings later: **Settings → Underway**.

== Frequently Asked Questions ==

= Do I need to connect an AI provider? =

No. Every widget works without AI. AI is purely additive — when a provider is connected via the WordPress AI Client, two widgets (Draft Sweeper and Habit Creator) gain extra polish: one-line draft summaries and starter questions. The rest is purely deterministic.

= I already have one of the standalone plugins (Draft Sweeper / Future Drafts / Ideas Inbox / Habit Creator) installed. =

Underway detects this and the bundled version of that widget will quietly step aside, so you don't get duplicate dashboard widgets. Deactivate the standalone plugin if you want Underway to take over.

= Where is my data stored? =

Each widget stores its own data the same way it always has — post meta, user meta, or post drafts. Underway's own options are limited to your widget toggles, AI preferences, and per-widget settings.

= What happens on uninstall? =

Underway removes its own options and scheduled events. Your ideas, drafts, and other user-authored content are left alone.

== Screenshots ==

1. Onboarding: choose which widgets to enable.
2. Dashboard with all four widgets active.
3. Settings page with widget toggles and AI controls.

== Changelog ==

= 0.1.0 =
* Initial bundled release combining Draft Sweeper, Future Drafts, Ideas Inbox, and Habit Creator.
* Unified settings page under Settings → Underway.
* Onboarding flow on activation.
* Shared AI provider detection and client wrapper.
* Standalone-plugin conflict detection so bundled and standalone copies coexist safely.

== Credits ==

* Draft Sweeper, Future Drafts, Habit Creator — Anne McCarthy
* Ideas Inbox — Kelly Hoffman
