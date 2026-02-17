# Changelog

All notable changes to **GenForm** are documented in this file.

Format: [Keep a Changelog](https://keepachangelog.com/en/1.1.0/) · Versioning: [Semantic Versioning](https://semver.org/spec/v2.0.0.html)

---

## [1.1.0] - 2026-02-17

### Added

- **Form Preview** — New admin page (`genform-preview`) renders the form inside a dashed preview container with a "Preview Mode" badge and submissions disabled. Accessible from:
  - Preview button in the builder toolbar (visible for saved forms).
  - Preview link in the All Forms list row actions.
- **GDPR / Consent Checkbox** — Per-form opt-in toggle with customizable consent text.
  - Admin: GDPR card in the Settings tab with enable checkbox + textarea for consent copy.
  - Frontend: Required checkbox rendered before the submit button when enabled.
  - Validation: Client-side (JS visual error with red outline) and server-side (`FormHandler.php` rejects submissions when consent is missing).
  - Persistence: `gdpr_enabled` and `gdpr_text` saved/loaded via `form-builder.js` `save()` and `loadInitialData()`.
- **Entry Detail Page** — Dedicated full-page view (`genform-entry-detail`) with:
  - Two-column grid layout: submission data table + sidebar (form name, date, status, IP, browser, OS, source URL).
  - Automatic `unread → read` status transition on view.
  - "View" action link added to the entries list table alongside existing Quick View modal.

### Changed

- Renamed all short/ambiguous PHP variables to descriptive names across 15 files:

  | Before | After | Files |
  |--------|-------|-------|
  | `$f`, `$genform_f` | `$form_row`, `$genform_form`, `$genform_field` | FormHandler, Builder, forms-list, form-builder, form-template |
  | `$d`, `$r_d` | `$entry_data`, `$resolved_data` | EntriesTable, entry-detail |
  | `$s`, `$genform_s` | `$genform_settings`, `$form_config` | form-builder, entry-detail |
  | `$n`, `$k`, `$v` | `$field_name`, `$key`, `$value` | ExportHandler, FormHandler |
  | `$p`, `$opts` | `$primary_color`, `$options` | Core |
  | `$genform_t`, `$genform_i` | `$genform_type`, `$genform_icon` | form-builder |
  | `$genform_ph`, `$genform_req`, `$genform_w`, `$genform_o` | `$genform_field_placeholder`, `$genform_field_required`, `$genform_field_width`, `$genform_option` | form-template |
  | `$genform_bsize`, `$genform_bweight`, `$genform_balign` | `$genform_base_font_size`, `$genform_base_font_weight`, `$genform_submit_align` | form-template, Shortcode |
  | `$f_name`, `$f_email` | `$from_name`, `$from_email` | Email |
  | `$unr`, `$trs`, `$cur`, `$fs` | `$total_unread`, `$total_trash`, `$current_status`, `$available_forms` | EntriesTable |

- Converted all legacy `array()` calls to short array `[]` syntax in Block, Shortcode, Core, EntriesTable, and form-builder view.
- Updated `form-builder.js` `save()` and `loadInitialData()` to include `gdpr_enabled` and `gdpr_text`.

### Files Modified

| File | Scope |
|------|-------|
| `genform.php` | Version bump `1.0.0` → `1.1.0` (header + constant) |
| `readme.txt` | Version bump + full changelog rewrite |
| `includes/Core.php` | Added preview + entry-detail admin pages; renamed variables; syntax conversion |
| `includes/Handlers/FormHandler.php` | Renamed variables; added GDPR server-side validation |
| `includes/Admin/Builder.php` | Renamed variables |
| `includes/Admin/EntriesTable.php` | Full naming refactor; added entry-detail "View" link; syntax conversion |
| `includes/Handlers/ExportHandler.php` | Renamed variables |
| `includes/Integrations/Email.php` | Renamed `$f_name` → `$from_name`, `$f_email` → `$from_email` |
| `includes/Integrations/Block.php` | Converted `array()` → `[]` |
| `includes/Integrations/Shortcode.php` | Syntax conversion; renamed `$bsize` → `$base_font_size`, `$bweight` → `$base_font_weight` |
| `admin/views/form-builder.php` | Added Preview button + GDPR card; renamed variables; syntax conversion |
| `admin/views/form-preview.php` | **New file** — form preview page |
| `admin/views/entry-detail.php` | **New file** — entry detail page |
| `admin/views/forms-list.php` | Renamed `$genform_fs` → `$all_forms` |
| `public/views/form-template.php` | Added GDPR checkbox rendering; renamed variables |
| `assets/js/form-builder.js` | Added GDPR save/load in `save()` and `loadInitialData()` |
| `assets/js/frontend.js` | Added GDPR consent validation with visual error feedback |

---

## [1.0.0] - 2026-01-15

### Added

- **Drag-and-drop form builder** with sidebar field palette, sortable canvas, and per-field edit modal.
- **10 field types:** Text, Email, Textarea, Number, Select (with option management), Radio, Checkbox, Date, URL, Phone.
- **Per-field settings:** Label, name, placeholder, required toggle, column width, CSS class, default value.
- **Form settings:** Submit button text and alignment, post-submission behavior (success message or redirect), base font size and weight.
- **Email notification engine** with dynamic template tags (`{form_name}`, `{entry_id}`, `{admin_email}`, `{site_title}`, `{all_fields}`, `{field_*}`).
- **Configurable sender identity** per form and globally via Settings page.
- **Gutenberg Block** (`genform/form-block`) with form picker in Inspector Controls.
- **Shortcode** (`[genform id="X"]`) with one-click copy button on All Forms page.
- **AJAX-powered submissions** with inline success/error messages and optional redirect.
- **Entry management** built on `WP_List_Table` with:
  - Search, form-filter dropdown, status views (All / Unread / Trash).
  - Quick-view modal, entry preview column, sortable columns.
  - Bulk actions: Read, Unread, Trash, Restore, Delete.
- **CSV export** with dynamically discovered column headers, batched streaming, and UTF-8 BOM for Excel.
- **Honeypot anti-spam** — hidden field silently rejects bot submissions.
- **IP-based rate limiting** — 5 submissions per minute per visitor via WordPress transients.
- **Device detection** utility (`DetectionHelper`) recording browser, OS, IP, and referrer URL.
- **Dashboard widget** showing total forms, total entries, and last 5 submissions.
- **Admin Bar menu** with quick links to All Forms and Entries.
- **Global Settings page** with reCAPTCHA keys, brand primary color, default email identity, and asset optimization toggle.
- **Form duplication** from the All Forms page.
- **Dynamic brand styling** — admin and frontend accent colors derived from the global primary color setting.
- **Conditional asset loading** — CSS/JS only enqueued on pages containing a form.
- **Automatic version upgrade** — `dbDelta` runs on version change to keep schema in sync.
- **Clean uninstall** — drops custom tables, removes options and rate-limiting transients.
- **PSR-4 autoloading** via Composer with namespaced PHP 8.3 codebase.
- **Full i18n support** — all user-facing strings wrapped in `esc_html__()` / `esc_html_e()` with `genform` text domain.
- **SCSS build pipeline** — `npm run watch` / `npm run build` for admin and frontend stylesheets.
