=== GenForm - Drag & Drop Form Builder ===
Contributors: arifrahman1
Tags: form builder, contact form, drag and drop, forms, email
Requires at least: 6.0
Tested up to: 6.9
Stable tag: 1.1.0
Requires PHP: 8.3
License: GPLv3 or later
License URI: https://www.gnu.org/licenses/gpl-3.0.html

A lightweight drag-and-drop form builder for WordPress. Build contact forms, feedback forms, and more — no coding, no bloat, no limits.

== Description ==

**GenForm** lets you create beautiful, responsive forms in minutes. It is designed for WordPress site owners, freelancers, and agencies who want a fast, no-bloat form builder that just works — right out of the box.

No page reloads. No premium upsells. No learning curve.

== 🖱️ Build Forms Visually ==

* Drag-and-drop builder with real-time field reordering.
* 10 built-in field types: Text, Email, Textarea, Number, Select, Radio, Checkbox, Date, URL, Phone.
* Per-field customization: labels, placeholders, required toggle, CSS classes, and column width (25%, 50%, 75%, 100%).
* Default values and multi-option management for Select, Radio, and Checkbox fields.
* Live form preview from the builder — see how your form looks before publishing.

== 📬 Smart Entry Management ==

* Centralized submissions dashboard built on `WP_List_Table`.
* Filter entries by specific form, status (All / Unread / Trash), or search keyword.
* Quick-view modal for rapid entry scanning without leaving the list.
* Dedicated entry detail page with a two-column layout: full submission data alongside metadata (IP, browser, OS, source URL).
* Unread indicator badge — entries auto-mark as "read" when viewed.
* Bulk actions: Read, Unread, Trash, Restore, Delete Permanently.
* One-click CSV export — per-form or all entries combined (UTF-8 + BOM for Excel compatibility).

== ✉️ Flexible Email Notifications ==

* Automatic admin email on every new submission.
* Dynamic template tags: `{form_name}`, `{entry_id}`, `{admin_email}`, `{site_title}`, `{all_fields}`, and per-field `{field_*}` tags.
* Customizable subject, body, sender name, sender email, and reply-to address per form.
* Global sender identity defaults in Settings → no repeat configuration.
* HTML email formatting with structured data tables.

== 🎨 Embed Anywhere ==

* **Gutenberg Block** — "GenForm" block with form picker in the sidebar.
* **Shortcode** — `[genform id="X"]` for Classic Editor, widgets, and page builders.
* Per-form typography settings: base font size (12–24px) and font weight (300–700).
* Customizable submit button: text, alignment (left, center, right, full-width).
* Post-submission behavior: display a success message **or** redirect to a custom URL.
* Conditional asset loading — CSS/JS only enqueued when a form is present on the page.

== 🛡️ Security & Compliance ==

* Honeypot anti-spam field (invisible to users, catches bots silently).
* IP-based rate limiting: 5 submissions per minute per visitor.
* Full WordPress nonce verification on every submission.
* Server-side GDPR consent validation — configurable per form with customizable consent text.
* reCAPTCHA v2/v3 key storage in global settings (integration-ready).
* Input sanitization (`sanitize_text_field`, `absint`, `sanitize_email`) and output escaping (`esc_html`, `esc_attr`, `esc_url`) on every value.
* Clean uninstall — all tables, options, and transients are removed when the plugin is deleted.

== ⚙️ Admin Experience ==

* Modern admin interface with card-based layout and custom branding.
* Dashboard widget showing total forms, total entries, and the 5 most recent submissions.
* Admin Bar shortcut menu for quick access to All Forms and Entries.
* Tabbed form builder: Fields → Settings → Notifications.
* Global Settings page: brand color, default email identity, reCAPTCHA keys, and asset optimization toggle.
* Form duplication with one click from the All Forms page.
* Copy-to-clipboard shortcode button for each form.
* Device metadata collection: browser, OS, IP address, and source page URL for every entry.

== 🏗️ Built for Developers ==

* PSR-4 autoloading via Composer.
* Namespaced PHP 8.3 codebase — no function prefix collisions.
* Singleton Core with modular handler architecture.
* Custom database tables with `dbDelta` — zero post-type pollution.
* WordPress Coding Standards compliant.
* Translation-ready (full i18n with `genform` text domain).
* SCSS source files with `npm run watch` for asset compilation.

== Installation ==

= Automatic Installation =

1. Log in to your WordPress admin panel.
2. Go to **Plugins → Add New**.
3. Search for **GenForm**.
4. Click **Install Now**, then **Activate**.

= Manual Installation =

1. Download the plugin `.zip` file.
2. Go to **Plugins → Add New → Upload Plugin**.
3. Select the downloaded file and click **Install Now**.
4. Activate the plugin.

= Getting Started =

1. Go to **GenForm → Add New** in your dashboard.
2. Enter a form title (e.g., "Contact Us").
3. Drag fields from the sidebar onto the canvas, then click any field to customize it.
4. Switch to the **Settings** tab to configure the submit button, success message, redirect URL, typography, and GDPR consent.
5. Switch to the **Notifications** tab to configure email alerts with template tags.
6. Click **Save Form**.
7. Embed your form using the **GenForm Gutenberg block** or copy the `[genform id="X"]` shortcode from the All Forms page.

== Frequently Asked Questions ==

= Is GenForm free? =

Yes. GenForm is 100% free and open source under GPLv3.

= How do I add a form to my page? =

Two ways:

1. **Block Editor (Gutenberg):** Add the "GenForm" block and select your form from the sidebar dropdown.
2. **Shortcode:** Copy `[genform id="X"]` from the All Forms page and paste it into any post, page, or widget.

= Where are submissions stored? =

In your own WordPress database, in custom tables (`wp_genform_entries`). Nothing is sent to external servers.

= Can I export submissions? =

Yes. Click the **Export CSV** button on the Entries page. You can export all entries or filter by a specific form first.

= Does it support GDPR? =

Yes. Each form has a GDPR / Consent toggle in the Settings tab. When enabled, a required consent checkbox appears before the submit button. The consent text is fully customizable.

= Does it work with my theme? =

GenForm renders clean, semantic HTML that inherits your theme's typography and styles. You can also fine-tune font size, weight, and submit button alignment per form.

= Is it mobile responsive? =

Yes. All forms are fully responsive. Fields automatically stack on smaller screens using percentage-based widths.

= Does it slow down my site? =

No. GenForm only loads its CSS and JavaScript on pages that actually contain a form — zero overhead on every other page.

= How does the anti-spam work? =

GenForm uses a two-layer approach:

1. **Honeypot field** — a hidden input that bots fill in but real users never see. Submissions with the honeypot filled are silently rejected.
2. **Rate limiting** — each IP address is limited to 5 submissions per minute. Excessive attempts receive a "try again later" message.

No CAPTCHAs, no friction for your visitors.

== Screenshots ==

1. Drag-and-drop form builder with available field types on the left and the canvas on the right.
2. Form settings panel — submission behavior, typography, and GDPR consent configuration.
3. Email notification settings with dynamic template tags.
4. Entries management dashboard with filtering, search, and bulk actions.
5. Entry detail page showing submitted data and metadata sidebar.
6. Form preview page — see your form exactly as visitors will.
7. Dashboard overview widget with recent submissions.

== Changelog ==

= 1.1.0 - 2026-02-17 =

* New: Form Preview — preview any saved form from the builder or the All Forms page before publishing.
* New: GDPR / Consent Checkbox — per-form toggle with customizable consent text validated on both client and server.
* New: Entry Detail Page — dedicated full-page view with two-column layout (submission data + metadata sidebar) and auto-read marking.
* Enhancement: Added Preview button to the form builder toolbar and Preview link to the All Forms row actions.
* Enhancement: Added GDPR / Consent settings card to the form builder Settings tab.
* Enhancement: Added frontend GDPR consent validation in JavaScript with visual error feedback.
* Update: Updated form builder JS to persist GDPR settings on save and load.

= 1.0.0 - 2026-01-15 =

* New: Drag-and-drop form builder with 10 field types.
* New: Gutenberg Block and Shortcode integration.
* New: AJAX-powered submissions with success message or redirect.
* New: Entry management with quick-view modal, search, filtering, and bulk actions.
* New: CSV export with UTF-8 BOM for Excel compatibility.
* New: Email notifications with dynamic template tags.
* New: Honeypot anti-spam and IP-based rate limiting.
* New: Dashboard overview widget and Admin Bar menu.
* New: Global settings — brand color, default email identity, reCAPTCHA keys.
* New: Clean uninstall with full data removal.
