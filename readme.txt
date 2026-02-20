=== GenForm - Drag & Drop Form Builder ===
Contributors: arifrahman1
Tags: contact form, form builder, drag and drop, forms, email
Requires at least: 6.0
Tested up to: 6.9
Stable tag: 1.2.0
Requires PHP: 8.3
License: GPLv3 or later
License URI: https://www.gnu.org/licenses/gpl-3.0.html

The lightweight drag-and-drop form builder for WordPress. Create contact forms, feedback forms, bookings, and more — no coding required.

== Description ==

= WordPress Contact Form Builder Plugin =

**GenForm** is a modern, lightweight WordPress form builder that lets you create beautiful, responsive forms in minutes — with an intuitive drag-and-drop interface.

Whether you need a simple contact form, event RSVP, job application, booking request, or lead-generation form, GenForm gives you everything you need right out of the box.

= Why Choose GenForm Over Other Form Plugins? =

Most WordPress form plugins overwhelm you with heavy page loads or confusing dashboards. GenForm takes a different approach:

🚀 **Feature-Packed** — 12 field types, 16+ templates, entry management, email notifications, CSV export, and more — all included.
⚡ **Lightweight & Fast** — CSS and JavaScript only load on pages that actually contain a form. Zero overhead on every other page.
🔒 **Privacy First** — All submissions are stored in your own WordPress database. Nothing is sent to any external server.
🛡️ **Secure by Default** — Built-in honeypot spam protection, IP rate limiting, nonce verification, and full input sanitization from day one.
🙌 **No Learning Curve** — If you can drag and drop, you can build a form. It's that simple.

= Drag & Drop Form Builder =

Build any form visually — no code, no complexity.

* Drag fields from the sidebar onto the canvas and reorder them in real time.
* **12 field types**: Text, Email, Textarea, Number, Select, Radio, Checkbox, Date, URL, Phone, Hidden, and Password.
* Customize every field: label, placeholder, help text, required toggle, CSS class, and column width.
* **6 column-width options** (25%, 33%, 50%, 67%, 75%, 100%) to create multi-column layouts.
* Clone any field with its full configuration in one click.
* Type-specific controls: textarea rows, number min/max/step values.
* Default values and multi-option management for Select, Radio, and Checkbox fields.

= 16+ Ready-Made Form Templates =

Skip the blank canvas and start with a professionally designed template. GenForm ships with **16+ templates across 7 categories** — just pick one, customize the text, and publish.

* **General** — Simple Contact, Event RSVP, Volunteer Signup.
* **Business** — Support Ticket, Job Application, Request a Quote, Bug Report.
* **Booking** — Restaurant Reservation, Appointment Booking, Hotel Reservation.
* **Marketing** — Newsletter Signup, Lead Generation, Event Registration.
* **Feedback** — Customer Feedback.
* **Education** — Course Enrollment.
* **Healthcare** — Patient Intake.

Each template comes pre-configured with the right fields, sensible validation, and polished submit-button layout.

= Smart Entry Management =

Every form submission is captured, organized, and easy to act on — all inside your WordPress dashboard.

* Familiar list-table interface — search, filter by form, and switch between All / Unread / Trash views.
* **Quick-View popup** — scan an entry without leaving the list.
* **Dedicated detail page** — see full submission data alongside metadata like IP address, browser, operating system, and source page URL.
* Unread badge indicator — entries auto-mark as "read" when viewed.
* Bulk actions: Mark Read, Mark Unread, Trash, Restore, Delete Permanently.
* **One-click CSV export** — per form or all entries combined, with Excel-compatible formatting.

= Email Notifications =

Get notified instantly every time someone submits a form.

* Automatic admin email on every new submission.
* **Dynamic template tags**: `{form_name}`, `{entry_id}`, `{admin_email}`, `{site_title}`, `{all_fields}`, and per-field `{field_*}` tags.
* Customizable subject line, email body, sender name, sender email, and reply-to address — all configurable per form.
* Global sender identity defaults in Settings — set it once, apply everywhere.
* Clean HTML emails with structured data tables.

= Embed Anywhere — Gutenberg, Shortcode & Page Builders =

* **Gutenberg Block** — add the "GenForm" block and pick your form from the sidebar.
* **Shortcode** — `[genform id="X"]` works in Classic Editor, text widgets, and any page builder (Elementor, Divi, Beaver Builder, etc.).
* Per-form typography: choose your font size (12–24 px) and font weight (300–700).
* Customizable submit button: text content and alignment (left, center, right, full-width).
* Post-submission behavior: show a success message **or** redirect to a custom URL.

= Anti-Spam Protection (No CAPTCHAs Required) =

Keep spam out without annoying your visitors.

* **Honeypot field** — a hidden input that bots fill in but real users never see. Any flagged submission is silently rejected.
* **IP rate limiting** — each IP address is capped at 5 submissions per minute. Excessive attempts get a "please try again later" message.
* reCAPTCHA v2/v3 key storage in global settings for additional protection when needed.

No puzzles, no image grids, no friction — your visitors just submit the form.

= GDPR & Privacy Compliance =

* Per-form GDPR consent checkbox with fully customizable text.
* Consent is validated on both the browser and the server — visitors cannot bypass it.
* All data stays in your WordPress database — nothing is sent to third-party servers.
* Clean uninstall removes all plugin data when you delete GenForm.

= Live Form Preview =

Preview any saved form exactly as your visitors will see it — directly from the builder or the All Forms page. Preview mode disables submissions so you can review the design without creating test entries.

= Beautiful Admin Dashboard =

* Modern admin interface with a clean, card-based layout.
* **Dashboard widget** — see total forms, total entries, and the 5 most recent submissions at a glance.
* **Admin Bar shortcuts** — quick links to All Forms and Entries from any admin page.
* Tabbed form builder: Fields → Settings → Notifications — everything in one place.
* Global Settings page: brand accent color, default email identity, reCAPTCHA keys, and asset optimization toggle.
* Form duplication and one-click shortcode copy on the All Forms page.

= Works With Your Favorite Tools =

GenForm integrates seamlessly with your WordPress setup:

* ✅ **Gutenberg** — dedicated block with form picker.
* ✅ **Classic Editor** — embed via shortcode.
* ✅ **Elementor, Divi, Beaver Builder** — paste the shortcode in any text/shortcode widget.
* ✅ **Any Theme** — outputs clean, semantic HTML that inherits your theme's styles.
* ✅ **Multisite Compatible** — works on WordPress multisite installs.

== Installation ==

= From Your WordPress Dashboard (Recommended) =

1. Go to **Plugins → Add New**.
2. Search for **GenForm**.
3. Click **Install Now**, then **Activate**.

= Manual Upload =

1. Download the `.zip` file from WordPress.org.
2. Go to **Plugins → Add New → Upload Plugin**.
3. Upload the file and click **Install Now**.
4. Activate the plugin.

= Quick-Start Guide — Your First Form in 2 Minutes =

1. Go to **GenForm → Add New** in your WordPress admin.
2. Choose **Start Blank** or pick a template from the library.
3. Drag fields onto the canvas — click any field to customize its label, placeholder, and settings.
4. Open the **Settings** tab to configure your submit button, success message (or redirect URL), and GDPR consent.
5. Open the **Notifications** tab to set up email alerts using tags like `{form_name}` and `{all_fields}`.
6. Click **Save Form**.
7. Add the **GenForm block** in Gutenberg, or copy the `[genform id="X"]` shortcode and paste it anywhere.

Done — your form is live! ✅

== Frequently Asked Questions ==

= Is GenForm free to use? =

Yes — GenForm is free and open source under GPLv3. All the features listed on this page are included in the free plugin. Install it and start building forms right away.

= How do I add a form to my page? =

Two ways:

1. **Block Editor (Gutenberg):** Add the "GenForm" block and pick your form from the sidebar dropdown.
2. **Shortcode:** Copy `[genform id="X"]` from the All Forms page and paste it into any post, page, widget, or page builder module.

= Does it work with Elementor, Divi, or other page builders? =

Yes. Paste the `[genform id="X"]` shortcode into any text element or shortcode widget in your preferred page builder.

= Where are form submissions stored? =

All submissions are stored securely in your own WordPress database (in a custom `wp_genform_entries` table). Nothing is sent to any external server.

= Can I export submissions to a spreadsheet? =

Yes. On the **Entries** page, click the **Export CSV** button. You can export all entries or filter by a specific form first. The export includes UTF-8 BOM for seamless Excel and Google Sheets compatibility.

= Does it support GDPR consent? =

Yes. Open your form's **Settings** tab and toggle on the GDPR / Consent checkbox. A required consent field with your custom text appears before the submit button. Consent is validated on both the client and server.

= Will it work with my theme? =

Yes. GenForm outputs clean, semantic HTML that inherits your theme's typography and styles. You can also fine-tune font size, weight, and submit-button alignment per form.

= Is it mobile responsive? =

Absolutely. All forms are fully responsive and adapt to any screen size using percentage-based column widths.

= Will it slow down my website? =

No. GenForm's CSS and JavaScript only load on pages that contain a form — there is zero impact on every other page.

= How does the spam protection work? =

GenForm uses a two-layer approach that requires no extra setup:

1. **Honeypot field** — bots fill in a hidden input that real visitors never see. Flagged submissions are silently discarded.
2. **IP rate limiting** — each IP is limited to 5 submissions per minute via WordPress transients.

No CAPTCHAs, no annoying puzzles — your visitors submit forms with zero friction.

= What happens if I uninstall GenForm? =

When you **delete** GenForm from the Plugins page, all custom database tables, saved options, and rate-limiting transients are removed automatically — leaving your WordPress installation clean.

== Screenshots ==

1. Drag-and-drop form builder with field types on the left and the canvas on the right.
2. Form settings panel — submit button, success message, typography, and GDPR consent.
3. Email notification settings with dynamic template tags.
4. Entries management dashboard with search, filtering, and bulk actions.
5. Entry detail page — full submission data with a metadata sidebar.
6. Live form preview — see your form exactly as visitors will.
7. Dashboard overview widget with recent submissions at a glance.

== Changelog ==

= 1.2.0 - 2026-02-20 =

* New: Templates Library — 16+ pre-built form templates with one-click import from the Add New Form page.
* New: Add New Form chooser — a modal with two paths: start blank or browse the template library.
* New: Hidden and Password field types for registration forms and tracking parameters.
* New: Help text setting — add descriptive guidance below any field.
* New: Field duplication — clone any field with its full configuration in one click.
* New: Type-specific settings — configurable textarea rows, number min/max/step.
* Enhancement: Improved plugin compatibility to ensure complete stability with your other themes and plugins.
* Enhancement: Upgraded the form builder engine for a noticeably faster and more reliable drag-and-drop experience.
* Enhancement: Optimized plugin performance for faster loading times in your WordPress dashboard.
* Enhancement: Expanded width selector to 6 options (Full, 3/4, 2/3, 1/2, 1/3, 1/4) with frontend multi-column layout.
* Enhancement: Submit button loading spinner and message fade-in animations.
* Enhancement: Renamed "Meta Key" to "Field Name" with descriptive tooltip.
* Enhancement: Field settings panel now displays field type icon as header.
* Enhancement: Upgraded empty canvas with illustration and welcoming copy.
* Enhancement: Moved entry-detail inline styles to proper SCSS classes.
* Update: Full i18n coverage — all builder and frontend JS strings are now translatable.

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