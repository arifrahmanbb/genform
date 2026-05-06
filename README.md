<p align="center">
  <strong>GenForm — Drag & Drop Form Builder for WordPress</strong><br>
  <em>Build beautiful forms in minutes. No coding. No bloat. Just drag and drop.</em>
</p>

<p align="center">
  <a href="https://wordpress.org/plugins/genform/"><img src="https://img.shields.io/wordpress/plugin/v/genform?label=version&color=6366f1" alt="Plugin Version"></a>
  <a href="https://wordpress.org/plugins/genform/"><img src="https://img.shields.io/wordpress/plugin/wp-version/genform?label=WordPress&color=21759b" alt="WordPress"></a>
  <a href="https://www.php.net/"><img src="https://img.shields.io/badge/PHP-%3E%3D8.3-8892BF" alt="PHP"></a>
  <a href="https://www.gnu.org/licenses/gpl-3.0.html"><img src="https://img.shields.io/badge/license-GPLv3-green" alt="License"></a>
</p>

---

## What is GenForm?

**GenForm** is a modern, lightweight WordPress form plugin. Create contact forms, feedback forms, booking requests, job applications, and more — using an intuitive drag-and-drop builder. No page reloads and absolutely no learning curve.

Packed with features right out of the box — **13 field types** (including Section Break), 16+ templates, entry management, email notifications, CSV export, JSON import/export, reCAPTCHA, inline validation, dark mode, and more. All free.

---

## Why Choose GenForm?

| Pain Point | GenForm's Answer |
|---|---|
| Plugins that feel incomplete out of the box | **Feature-packed** — 12 field types, templates, entries, CSV export, email notifications, reCAPTCHA, and more included |
| Plugins that load scripts on every page | CSS & JS load **only on pages with a form** — zero impact elsewhere |
| Submissions sent to external servers | All data stays in **your WordPress database** — nothing leaves your server |
| Complex dashboards with a steep learning curve | Intuitive **drag-and-drop builder** with live preview — ready in 2 minutes |
| Spam flooding your inbox | **Honeypot + IP rate limiting + reCAPTCHA v2** — layered protection with no friction |
| GDPR compliance headaches | **Per-form consent checkbox** with customizable text, validated client + server |

---

## Features

### Drag & Drop Form Builder

- **13 field types:** Text, Email, Textarea, Number, Select, Radio, Checkbox, Date, URL, Phone, Hidden, Password, **Section Break**
- **Section Break field** — add titled dividers with an optional description to group related fields into named sections
- Per-field customization: labels, placeholders, help text, required toggle, CSS classes
- **6 column-width options** (25% to 100%) for multi-column layouts
- One-click field cloning with full configuration
- Type-specific controls: textarea rows, number min/max/step, and **text/email/URL/tel min/max character limits**
- Default values and multi-option management for Select, Radio, and Checkbox fields
- Live form preview before publishing
- **Inline field validation** — per-field error messages appear on blur for required, email, URL, phone, number, and character-length rules
- **URL parameter prefill** — populate fields from query strings (e.g. `?gfm_name=John`) for landing pages and CRM flows
- **Dark mode** — forms adapt automatically when the visitor's OS is set to dark mode

### 16+ Ready-Made Templates

Pre-built templates across **7 categories** — General, Business, Booking, Marketing, Feedback, Education, and Healthcare. Pick one, customize, and publish in seconds.

### Smart Entry Management

- Search, filter by form, and switch between All / Unread / Trash views
- **Star important entries** — flag submissions you want to follow up on; persists across sessions
- Quick-view popup and dedicated detail page with metadata (IP, browser, OS, source URL)
- Unread badge with auto-read marking
- Bulk actions: Mark Read, Mark Unread, Trash, Restore, Delete Permanently
- **One-click CSV export** — per form or all entries, with Excel-compatible formatting

### Email Notifications

- Automatic admin notification email on every new submission
- **Confirmation email to the submitter** — configure subject, body, and which field holds their email, all per form
- Dynamic template tags: `{form_name}`, `{entry_id}`, `{admin_email}`, `{site_title}`, `{all_fields}`, `{field_*}`
- Customizable subject, body, sender name, sender email, and reply-to per form
- Global sender defaults in Settings — configure once, use everywhere

### Form Import & Export

- **Export any form as JSON** — one click from the All Forms page
- **Import a JSON file** — upload a previously exported form from any GenForm install to recreate it instantly
- Move forms between sites without losing a single field or setting

### Anti-Spam & Security

- **Honeypot field** — invisible to real users, catches bots silently
- **IP rate limiting** — capped at 5 submissions per minute per visitor
- **Google reCAPTCHA v2** — add keys in Settings, enable per form with a single checkbox; token verified server-side on every submission
- WordPress nonce verification on every request
- Full input sanitization and output escaping

### Embed Anywhere

- **Gutenberg Block** with form picker
- **Shortcode** `[genform id="X"]` for Classic Editor, widgets, and all page builders (Elementor, Divi, Beaver Builder, etc.)
- Per-form typography (font size + weight) and submit button customization (text + alignment)
- Post-submission: success message or redirect to custom URL
- **Enable / disable forms** — toggle any form on or off without deleting it. Inactive forms show nothing to visitors.

### GDPR & Privacy Compliance

- Per-form consent checkbox with fully customizable text
- Consent validated on both browser and server — cannot be bypassed
- All data stays in your WordPress database — nothing sent to third parties
- Clean uninstall removes all plugin data when deleted

### Admin Experience

- Modern card-based dashboard with custom brand color
- **Dashboard widget** — total forms, total entries, and 5 most recent submissions at a glance
- **Admin Bar shortcuts** to All Forms and Entries from any page
- Form duplication, JSON export, status toggle, and one-click shortcode copy on the All Forms page
- Tabbed form builder: Fields → Settings → Notifications — everything in one place

---

## Quick Start

```
1. Install & activate GenForm from Plugins → Add New
2. Go to GenForm → Add New
3. Choose "Start Blank" or pick a template
4. Drag fields → click to customize
5. Settings tab → submit button, success message, reCAPTCHA, GDPR
6. Notifications tab → admin alerts + optional confirmation email to submitter
7. Save Form → embed via block or [genform id="X"] shortcode
```

**Your form is live in 2 minutes.**

---

## Compatibility

GenForm works seamlessly with:

- Gutenberg (Block Editor)
- Classic Editor
- Elementor
- Divi Builder
- Beaver Builder
- Any WordPress Theme
- WordPress Multisite

---

## Installation

### From WordPress Dashboard

1. Go to **Plugins → Add New**
2. Search for **GenForm**
3. Click **Install Now** → **Activate**

### Manual Upload

1. Download the `.zip` from [WordPress.org](https://wordpress.org/plugins/genform/)
2. **Plugins → Add New → Upload Plugin**
3. Upload, install, and activate

---

## Changelog

### 1.4.0 — 2026-05-06

- **New:** Section Break field — add titled dividers between form sections to group related fields visually. Supports a title, optional description, and CSS class
- **New:** Inline field validation — real-time per-field error messages appear on blur for required fields, email, URL, phone, number, and character-length constraints
- **New:** URL parameter prefill — populate form fields from URL query strings (e.g. `?gfm_name=John`) for landing-page and CRM workflows
- **New:** Dark mode support — forms automatically switch to a dark colour palette when the visitor's OS is set to dark mode
- **Fix:** Rate limiting is now scoped per form and per IP address. Previously a single transient key was shared across all forms
- **Enhancement:** Email notifications are now sent asynchronously via WP-Cron, eliminating SMTP latency from the submission response time

### 1.3.0 — 2026-04-21

- **Fix:** reCAPTCHA keys saved in Settings are now fully enforced — the widget renders on the form and the token is verified server-side on every submission
- **New:** Form active/inactive toggle — enable or disable any form from the All Forms list without deleting it. Inactive forms display nothing to visitors
- **New:** Confirmation email to submitter — configure per-form auto-response with custom subject, body, and dynamic template tags
- **New:** Text/Textarea/Email/URL/Tel fields now support min/max character length validation via dedicated builder inputs
- **New:** Form JSON export — download any form's complete structure as a portable `.json` file
- **New:** Form JSON import — upload a previously exported JSON to recreate a form on any GenForm install
- **New:** Entry starring — flag important submissions with a star icon; starred state persists across sessions
- **Enhancement:** Database indexes added on `form_id` and `status` columns in the entries table for improved query performance at scale
- **Enhancement:** reCAPTCHA enable/disable toggle added to the form builder Settings tab (only shown when global keys are configured)
- **Enhancement:** `starred` column added to the entries table via non-destructive `dbDelta` upgrade

### 1.2.0 — 2026-02-20

- **New:** Templates Library — 16+ pre-built form templates with one-click import
- **New:** Add New Form chooser modal (blank form or template library)
- **New:** Hidden and Password field types
- **New:** Help text setting for any field
- **New:** One-click field duplication
- **New:** Type-specific settings (textarea rows, number min/max/step)
- **Enhancement:** Improved plugin compatibility and stability with other themes and plugins
- **Enhancement:** Upgraded the form builder engine for a faster and more reliable drag-and-drop experience
- **Enhancement:** 6-option column width selector with multi-column frontend layout
- **Enhancement:** Submit button loading spinner and message fade-in animations
- **Enhancement:** Field settings panel displays field type icon as header
- **Enhancement:** Upgraded empty canvas with illustration and welcoming copy
- **Update:** Full i18n coverage — all builder and frontend JS strings are now translatable

### 1.1.0 — 2026-02-17

- **New:** Form Preview — preview any saved form from the builder or All Forms page before publishing
- **New:** GDPR / Consent Checkbox — per-form toggle with customizable consent text validated on both client and server
- **New:** Entry Detail Page — dedicated full-page view with two-column layout and auto-read marking
- **Enhancement:** Preview button added to the form builder toolbar
- **Enhancement:** GDPR / Consent settings card added to the form builder Settings tab
- **Enhancement:** Frontend GDPR consent validation in JavaScript with visual error feedback

### 1.0.0 — 2026-01-15

- Initial release with drag-and-drop builder, Gutenberg block, shortcode, AJAX submissions, entry management, CSV export, email notifications, honeypot anti-spam, rate limiting, dashboard widget, and global settings.

---

## License

GenForm is free software released under the [GNU General Public License v3.0](https://www.gnu.org/licenses/gpl-3.0.html).

---

## Contributing

Contributions, bug reports, and feature requests are welcome. Open an issue or submit a pull request on [GitHub](https://github.com/arifrahman1/genform).

---

<p align="center">
  Built with care for the WordPress community<br>
  <a href="https://wordpress.org/plugins/genform/">WordPress.org</a> · <a href="https://profiles.wordpress.org/arifrahman1/">Author Profile</a>
</p>
