# GenForm — Completed Features
**Last Updated:** 2026-05-06 | **Current Version:** 1.4.0

All features listed here are fully shipped and live in the plugin.
Planned features live in [PLANNING.md](PLANNING.md).

---

## FREE — Completed

---

### 01 — Drag-Drop Form Builder

- Drag fields from sidebar onto canvas; reorder by dragging
- Click any field to open its settings panel (label, placeholder, required, help text, CSS class, width, default value)
- 6 column-width options: 25%, 33%, 50%, 67%, 75%, 100%
- Tab system: Fields | Email | Settings

---

### 02 — Field Types (13 total)

- Text, Email, Textarea, Number, Select, Radio, Checkbox, Date, URL, Tel, Hidden, Password
- **Section Break** — visual divider with title (`<h3>`) and optional description (`<p>`); no data capture; full builder settings panel; renders as `.gfm-type-section_break` in frontend

---

### 03 — Field Duplication

- `duplicateField()` deep-clones any field's full configuration and assigns a new unique ID
- One-click clone button on every field node in the builder canvas

---

### 04 — Client-Side Inline Validation

- `validateField()` runs on every field blur (capture phase) and on form submit
- Validates: required, email format, URL format, tel pattern, number min/max, minlength/maxlength
- Errors injected as `.gfm-error-message` spans with `role="alert"` for screen reader accessibility
- Success state (green border) applied on valid blur via `clearError()`
- All error strings localized via `wp_localize_script`
- Server-side validation remains as security fallback

---

### 05 — URL Parameter Prefill

- `prefillFromUrl()` runs on DOMContentLoaded
- Reads `URLSearchParams` and populates any field whose `name` matches `gfm_{key}`
- Works with text inputs, selects, radios, checkboxes
- Values sanitized and capped at 1000 characters

---

### 06 — AJAX Form Submission

- Nonce verification on every submission
- Honeypot field — hidden from real users, silently rejects bot submissions
- Success message or redirect to custom URL (configurable per form)
- Submit button loading spinner + disabled state during request
- Success/error message fade-in animation (`@keyframes gfm-fade-in`)

---

### 07 — Per-Form Rate Limiting

- 5 submissions per minute per IP address
- Scoped per-form per-IP: transient key is `genform_rate_{form_id}_{md5(ip)}`
- A user submitting multiple forms on the same page is not incorrectly blocked

---

### 08 — Google reCAPTCHA v2

- Site key and secret key configured in Global Settings
- Per-form enable/disable toggle in builder Settings tab (only shown when global keys are set)
- Token verified server-side on every submission

---

### 09 — GDPR Consent Checkbox

- Per-form toggle in builder Settings tab with fully customizable consent text
- Required field — validated on both client and server
- Cannot be bypassed

---

### 10 — Async Email Sending

- `Email::queue()` schedules both admin notification and confirmation email via `wp_schedule_single_event()`
- Two cron hooks: `genform_send_email_async` → `Email::sendAsync()`, `genform_send_confirmation_async` → `Email::sendConfirmationAsync()`
- Entry data re-fetched from DB in each async handler — no large transient storage
- Submission response returns to visitor immediately after entry is saved

---

### 11 — Admin Email Notification

- Sent on every new submission
- Configurable: subject, body, sender name, sender email, reply-to — all per form
- Dynamic template tags: `{form_name}`, `{entry_id}`, `{admin_email}`, `{site_title}`, `{all_fields}`, `{field_*}`
- Global sender identity defaults in Settings (configure once, apply everywhere)

---

### 12 — Confirmation Email to Submitter

- Per-form toggle in builder Notifications tab
- `gfm_conf_to_field` setting selects which form field holds the submitter's email address
- Configurable: subject, body, sender name, sender email
- Supports all dynamic template tags

---

### 13 — Min / Max Character Length

- Text, Textarea, Email, URL, Tel fields support `minlength` and `maxlength` attributes
- Configurable via dedicated builder inputs in the field settings panel
- Enforced by client-side validation and HTML5 native validation

---

### 14 — Entry Management

- WP_List_Table interface with All / Unread / Trash views
- Filter by form and status
- Search across entry data (`entry_data LIKE %s`)
- Star important entries (persists across sessions via `starred` column)
- Quick-view popup — scan entry without leaving the list
- Dedicated entry detail page — full field data with IP, browser, OS, source URL metadata
- Entries auto-mark as read when opened
- Bulk actions: Mark Read, Mark Unread, Trash, Restore, Delete Permanently

---

### 15 — CSV Export

- Streaming export with 500-row chunking
- UTF-8 BOM for seamless Excel and Google Sheets compatibility
- Export all entries or filter by form first

---

### 16 — Form JSON Import / Export

- Export any form's complete field schema and settings as a `.json` file (one click from All Forms list)
- Import a previously exported JSON to recreate the form on any GenForm install
- Import button in All Forms page header with file-picker integration

---

### 17 — Form Status Toggle

- Active / Inactive toggle per form on the All Forms list
- Inactive forms render nothing to visitors — no error, no empty wrapper

---

### 18 — Templates Library

- 16+ pre-built templates across 7 categories: General, Business, Booking, Marketing, Feedback, Education, Healthcare
- Modal-based chooser with "Start Blank" and "Browse Templates" paths
- One-click import creates a new form with all fields and settings pre-configured

---

### 19 — Forms List — Shortcode Copy + Submission Count

- Shortcode copy button (`gfm-shortcode-copy`) on every form row — one click copies `[genform id="X"]`
- Submission count column shows total entries as a clickable badge linking to filtered entries view

---

### 20 — Entry Starring

- Star icon on every entry row in the entries list
- Starred state stored in `starred` column (`wp_genform_entries`)
- Persists across sessions and users

---

### 21 — Dark Mode

- `@media (prefers-color-scheme: dark)` block in `frontend.css`
- Covers: form background, inputs, textarea, select, labels, field descriptions, choice controls, error/success message variants, error state inputs, success state inputs, section break border and text
- Automatic — no user or admin configuration needed

---

### 22 — Gutenberg Block

- Dedicated "GenForm" block with form picker in the block sidebar
- Server-side render delegates to shortcode handler
- Requires WordPress 6.0+

---

### 23 — Shortcode

- `[genform id="X"]` works in Classic Editor, text widgets, Elementor, Divi, Beaver Builder, and any page builder that accepts shortcodes

---

### 24 — Form Preview

- Preview any saved form from the builder toolbar or the All Forms row actions
- Submissions are disabled in preview mode
- Opens in a new tab using a preview-mode query parameter

---

### 25 — Dashboard Widget

- Total forms count, total entries count
- 5 most recent submissions with form name and timestamp

---

### 26 — Admin Bar Shortcuts

- Quick links to All Forms and Entries from any admin page via the WordPress Admin Bar

---

### 27 — Global Settings

- reCAPTCHA v2 site key and secret key
- Brand accent color
- Default sender name and sender email for all notifications
- Asset optimization toggle (load assets only on pages with a form)

---

### 28 — Empty States

- SVG illustration + "Create your first form" CTA on the empty forms list
- Empty canvas illustration + guidance copy in the builder when no fields are added

---

### 29 — Per-Form Typography + Submit Button

- Font size (12–24 px) and font weight (300–700) configurable per form
- Submit button: custom text, alignment (left, center, right, full-width)

---

### 30 — Clean Uninstall

- All custom DB tables, saved options, and rate-limiting transients removed automatically when plugin is deleted from Plugins page

---

### 31 — Onboarding Wizard

- 3-step overlay wizard shown on first activation (any GenForm admin page)
- Step 1: welcome screen with feature highlights
- Step 2: 8-category grid (general, business, booking, feedback, marketing, education, healthcare, blank)
- Step 3: notification email input (pre-filled with `admin_email`) → creates form from first template in chosen category and redirects to builder
- Completion state tracked in `genform_onboarding_complete` option
- Dismissable via skip link — sets completion flag without creating a form
- "Relaunch Setup Wizard" button in Settings page to re-trigger at any time

---

## PRO — Completed

---

### 01 — Conditional Logic (Field-Level)

- Show / hide fields based on rules (field value conditions)
- Supports: all, any logic; 9 comparison operators
- Visual rule builder panel rendered by `renderConditionalPanel()` in `Conditional_Engine.php`
- Rules stored per-field in form schema JSON

---

### 02 — Multi-Step Forms

- Page Break field triggers multi-step UI
- Progress bar displayed between steps
- Back / Next navigation

---

### 03 — Calculations

- Mathematical formula support for number fields
- Enables pricing calculators and quote builders

---

### 04 — Save & Resume

- Pause and return to a partially filled form via a resume link

---

### 05 — Form Abandonment Capture

- Captures partial entries before form submission

---

### 06 — File Upload

- Drag-drop file upload with validation and secure storage
- File metadata stored in `wp_genform_files`

---

### 07 — Signature Field

- Touch/mouse canvas signature capture

---

### 08 — Star Rating Field

- Clickable star rating input

---

### 09 — Repeater Field

- Add/remove repeating row groups of sub-fields

---

### 10 — Address Field

- Multi-subfield address: street, city, state, zip, country

---

### 11 — Rich Text Field

- WYSIWYG text editor input

---

### 12 — Survey Field

- Survey / Likert scale input type

---

### 13 — Stripe Payments

- Stripe PaymentIntent integration
- One-time and recurring payment support
- Payment data stored in `wp_genform_payments`

---

### 14 — PayPal Payments

- PayPal payment integration

---

### 15 — Coupon Codes

- Coupon/discount code support for payment forms

---

### 16 — Webhook Integration

- POST entry data to any external URL on form submission

---

### 17 — Google Sheets Integration

- Auto-append new entry as a row in a Google Sheet

---

### 18 — Slack Notification

- Send form submission summary to a Slack channel

---

### 19 — Mailchimp Integration

- Subscribe submitter to a Mailchimp audience on submission

---

### 20 — User Registration

- Create a WordPress user account from a form submission

---

### 21 — Post Submission

- Create a post or custom post type entry from a form submission

---

### 22 — Entry Editor

- Edit saved entry field data in the admin entry detail view

---

### 23 — PDF Generator

- Generate a PDF document from entry data

---

### 24 — Geolocation

- Capture submitter geolocation metadata with each entry

---

### 25 — Landing Pages

- Distraction-free standalone form URLs (no theme header/footer)
