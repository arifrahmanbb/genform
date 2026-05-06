# GenForm — Feature Planning
**Last Updated:** 2026-05-07

Planned features only. Nothing here is shipped yet. Completed features live in [ROADMAP.md](ROADMAP.md).
Items are listed in implementation priority order — work top to bottom.

---

## FREE FEATURES

---

### 01 — reCAPTCHA v3 + hCaptcha + Cloudflare Turnstile

**Priority:** Critical  
**Why:** reCAPTCHA v2 checkbox is the most friction-heavy CAPTCHA. v3 is invisible. Both WPForms and Fluent Forms include all three alternatives free. This is a spam-protection trust gap.

- Add reCAPTCHA v3 (invisible, score-based) alongside v2
- Add hCaptcha as privacy-first alternative
- Add Cloudflare Turnstile as frictionless alternative
- Global Settings: CAPTCHA type selector (v2 / v3 / hCaptcha / Turnstile)
- Per-form toggle stays — extend it to show type when globally configured
- Spam entries flagged with "Spam" status in entries list; not silently dropped

---

### 02 — Akismet Spam Filter

**Priority:** High  
**Why:** Akismet is pre-installed on most WordPress sites. Sophisticated bots bypass CAPTCHA — Akismet's content-based filtering catches them. WPForms and Fluent Forms both include this free.

- Check if Akismet is active; if not, show "install Akismet" suggestion in Security settings
- On submission call `akismet_comment_check()` with form data as "comment" context
- Flag Akismet-rejected entries with "Spam" status in entries list
- No hard dependency on Akismet — feature silently skips if plugin is inactive

---

### 03 — Submission Analytics Dashboard

**Priority:** High  
**Why:** Fluent Forms added a full free analytics dashboard in 2025. GenForm has only a basic dashboard widget. Users need a reason to log in and check form performance — analytics is that reason.

- New admin page: GenForm → Analytics
- Total submissions all-time and last 30 days
- Per-form submission bar chart
- Submissions over time line chart (last 30 days)
- Use Chart.js (no extra dependency — common in WP ecosystem)
- Advanced analytics (heatmaps, conversion rates, field drop-off) stay Pro

---

### 04 — Embed Wizard After Form Save

**Priority:** High  
**Why:** Users save a form and have no guidance on how to embed it. This is the biggest gap between form creation and first submission — the critical first-success moment.

- After clicking "Save Form", show a modal with:
  - Shortcode with one-click copy button
  - "Add Gutenberg Block" step-by-step instruction
  - "Use in Elementor / Divi" shortcode note
- Trigger via `genform_form_saved` JS event
- "Don't show again" dismiss option

---

### 05 — Last Submission Date on Forms List

**Priority:** High  
**Why:** Users need a quick health check on which forms are receiving traffic. Currently there is no date context on the forms list.

- Add "Last Submission" column to All Forms list
- `MAX(created_at)` per form, cached alongside existing submission count transient
- Display as human-readable relative time ("3 hours ago", "2 days ago")
- Empty if no submissions yet

---

### 06 — Template Search + Preview

**Priority:** Medium  
**Why:** With 16+ templates across 7 categories and no search, users must scroll everything manually. Template previews don't exist — users import blind.

- Client-side search by template name and category keyword (no server round-trip)
- Template preview: rendered screenshot or iframe in modal before import
- Badge "Requires Pro" on templates containing Pro-only field types (read `requires_pro` flag from template JSON)
- Promote "Start Blank" shortcut more prominently in the chooser

---

### 07 — Settings Page Restructure

**Priority:** Medium  
**Why:** Current Settings is a single flat page mixing spam, email, and appearance settings. Cognitively overloaded.

- Reorganize into tabs: General | Email | Spam Protection | Appearance
- Add "Send Test Email" button in Email tab
- Add reCAPTCHA connection test / validation button in Spam Protection tab
- No behavior changes — layout and discoverability only

---

### 08 — Undo / Redo in Builder

**Priority:** Medium  
**Why:** Any field move or deletion is permanent in the current session. Users fear experimentation. WPForms and Fluent Forms both have undo/redo.

- Client-side command history stack, maximum 30 states
- Track operations: add field, remove field, reorder field, update field property
- Store state as deep-cloned snapshots of the full fields array (not diffs)
- Keyboard shortcuts: Ctrl+Z (undo), Ctrl+Shift+Z (redo)
- Undo/redo buttons in builder toolbar

---

### 09 — Gutenberg Block — Live Preview + Create Form

**Priority:** Medium  
**Why:** Current block shows only a form picker dropdown with no preview. Users cannot see what their form looks like in the editor without visiting the frontend.

- Add `ServerSideRender` so the form renders inside the block editor
- Add "Create new form" button inside the block when no form is selected (links to form builder)
- Add wide / full alignment support to the block wrapper

---

### 10 — Entry Date Range Filter

**Priority:** Medium  
**Why:** Entries list has search but no date filter. Users managing high-volume forms cannot isolate submissions from a specific campaign period.

- Add From / To date pickers to the entries list filter bar
- Filter against `e.created_at`
- Integrate with existing form filter and status filter (all filters compose)
- Include date range parameters in CSV export

---

### 11 — Prev / Next Entry Navigation

**Priority:** Medium  
**Why:** Reviewing entries one by one requires returning to the list between each entry — no sequential navigation exists.

- Add "← Previous" and "Next →" links in the entry detail view
- Order by `created_at DESC` (same order as the entries list)
- Respect current list filter context when navigating

---

### 12 — Builder Tab Restructure

**Priority:** Medium  
**Why:** Current "Settings" tab mixes styling (font size, submit button alignment) with behavior (success message, redirect, GDPR). "Email" tab is separate but could be consolidated. Cognitive overload reduces form configuration success rates.

- Rename and reorganize builder tabs: Fields | Notifications | Confirmation | Design | Security
- Move font size, submit alignment, button text → Design tab
- Move GDPR settings → Security tab
- Move success message / redirect → Confirmation tab
- No new settings — layout and discoverability only

---

### 13 — Per-Form Color Override

**Priority:** Low  
**Why:** Brand color is currently global only. Multi-brand or multi-client sites need per-form color control.

- Color picker in builder "Design" tab (inherits global brand color by default)
- Outputs as inline CSS variable `--gfm-accent` on the form wrapper
- "Reset to global" link clears the override

---

### 14 — CC / BCC on Admin Notification

**Priority:** Low  
**Why:** Small teams often need a second recipient on form notifications without upgrading to Pro for multiple notifications.

- Add CC and BCC input fields to the builder Notifications tab
- Supports comma-separated email addresses
- Single admin notification remains in free — multiple independent notifications stay Pro

---

### 15 — Phone Field — Enhanced Validation

**Priority:** Low  
**Why:** The existing `tel` field uses a basic regex. Dedicated phone UX (icon, format hint) would match competitor parity.

- Rename display label from "Tel" to "Phone" with a phone icon in the builder sidebar
- Improve client-side regex to handle international formats more accurately
- Add format hint placeholder ("e.g. +1 (555) 000-0000")
- Country code selector (intl-tel-input) stays Pro

---

## PRO FEATURES

---

### 01 — Conditional Logic — Form-Level Outcomes

**Priority:** Critical  
**Why:** Field-level show/hide UI already exists. Extending to form-level outcomes (redirect, confirmation message) completes the engine and unlocks the most-requested Pro use cases.

- Conditional redirect: redirect to URL A if condition X, URL B if condition Y
- Conditional confirmation: show different thank-you message based on field values
- Connect conditional engine to multi-step page navigation (skip pages based on rules)

---

### 02 — Multi-Step — Step Validation + UX

**Priority:** Critical  
**Why:** Currently users can advance to the next step without valid data in the current step. Data is lost on back navigation. Both are broken behaviors for production use.

- Enforce field validation on "Next" click before advancing to next step
- Preserve all step data in JS memory when navigating back
- Configurable progress bar styles: steps indicator / percentage / filled bar
- Animated transitions between steps

---

### 03 — Webhook Delivery Log + Retry

**Priority:** High  
**Why:** Webhooks currently fire and forget. No visibility into failures. Businesses using webhook for CRM sync need delivery confirmation and retry.

- Admin log table: URL, HTTP status code, response body, timestamp per delivery attempt
- Retry mechanism: 3 attempts with exponential backoff via WP Cron
- "Test Webhook" button in builder that sends a sample payload
- Field mapping: select which form fields to include in payload and rename keys

---

### 04 — Pro Analytics Dashboard UI

**Priority:** High  
**Why:** `Charts.php` backend already returns daily entries, form breakdown, and payment stats via `genform_get_chart_data` AJAX. The data layer is done — it just needs a frontend.

- Dedicated "Reports" admin page under the Pro menu
- Connect `getChartData()` AJAX response to Chart.js charts
- Show: daily submissions chart, per-form breakdown, payment stats
- Add conversion rate per form (requires form view logging — see ### 08 below)

---

### 05 — Mailchimp — Field Mapping + Tags

**Priority:** High  
**Why:** Current Mailchimp integration auto-detects fields by name — brittle. No tag assignment. No double opt-in. Users cannot map their custom field names to Mailchimp merge fields.

- Visual field mapping UI: drag form field → Mailchimp merge field
- Static + dynamic tag assignment (from field values)
- Double opt-in toggle per form
- Audience group / segment selection

---

### 06 — ConvertKit / Kit Integration

**Priority:** High  
**Why:** ConvertKit is the #1 email platform for creators and bloggers — GenForm's largest addressable free user segment. No integration means losing every creator-market install to Fluent Forms.

- API key configuration in Settings
- Per-form: select form or sequence to subscribe to
- Field mapping: email field selector + optional first/last name
- Subscribe on form submission; handle errors gracefully

---

### 07 — PDF — Templates + Email Attachment

**Priority:** High  
**Why:** PDF generation exists but template system is unclear. No branded layouts. No ability to attach PDF to the confirmation email — the most-requested PDF use case.

- 3 PDF layout templates: Invoice, Certificate, Summary
- Logo upload for branded PDFs
- "Attach PDF to confirmation email" toggle per form
- "Download PDF" button in entry detail view

---

### 08 — Conversion Rate Tracking

**Priority:** High  
**Why:** Users cannot optimize forms without knowing view-to-submission conversion. This closes the biggest analytics gap vs. Fluent Forms Pro.

- Log form views via a lightweight JS beacon on form render
- Conversion rate = submissions / views per form
- Display per-form conversion % in Pro analytics dashboard
- Track conversion trend over time (last 30 days chart)

---

### 09 — ActiveCampaign Integration

**Priority:** Medium  
**Why:** ActiveCampaign is the dominant SMB CRM. Missing it means losing upgrade decisions from any sales/marketing team.

- API key + URL configuration in Settings
- Per-form: select list and optional automation to trigger
- Field mapping to ActiveCampaign custom fields
- Tag assignment from field values

---

### 10 — HubSpot Integration

**Priority:** Medium  
**Why:** HubSpot is the enterprise/SaaS standard. Agencies building client forms frequently need HubSpot contact sync.

- OAuth connection flow (connect / disconnect)
- Per-form: select HubSpot form or create contact directly
- Field mapping to HubSpot contact properties

---

### 11 — Stripe — Strategic Decision + Implementation

**Priority:** Medium  
**Why:** WPForms and Fluent Forms both offer Stripe free with a transaction fee. GenForm locking Stripe fully behind Pro may be losing payment-motivated installs.

- **Decision required before implementation:** free with 2% GenForm fee vs. fully Pro
- If free-with-fee: implement Stripe Connect to collect the platform fee
- If fully Pro: add coupon code support and Stripe webhooks for refund/failure handling

---

### 12 — Entry Import (CSV)

**Priority:** Medium  
**Why:** Agencies migrating from Gravity Forms or WPForms cannot bring their historical data. This is a blocker for plugin switches.

- CSV upload with field mapping UI
- Support WPForms and Gravity Forms export format detection
- Validate and preview rows before import
- Import into `wp_genform_entries` with correct `form_id` and `entry_data` JSON

---

### 13 — Calculations Formula Builder UI

**Priority:** Medium  
**Why:** Calculations engine exists but likely uses a plain text input for formulas. Without a visual builder, non-developers cannot create pricing calculators.

- Visual formula builder: field references + operators (+, -, ×, ÷, %) + function library (SUM, ROUND, IF)
- Real-time formula preview in builder as fields are configured
- Number formatting output: currency, percentage, decimal places

---

### 14 — Save & Resume — Expiry + Admin View

**Priority:** Low  
**Why:** Save & Resume exists but partial entries need lifecycle management — expiry and admin visibility.

- Store partial entries in `wp_genform_entries` with status `draft`
- Generate time-limited resume URL (72-hour expiry)
- Show draft entries in admin with "Incomplete" badge
- WP Cron job to clean up expired drafts automatically

---

### 15 — Signature — Export + Timestamp

**Priority:** Low  
**Why:** Signature field captures but cannot export. Legal/compliance workflows need the signature embedded in PDFs with metadata.

- Export signature as embedded PNG in PDF generation (depends on ### 07)
- Store timestamp + submitter IP alongside signature data in entry metadata

---

### 16 — Form Abandonment — Per-Field Save + Follow-Up

**Priority:** Low  
**Why:** Current abandonment capture reliability is unclear. Page-unload capture is not guaranteed in modern browsers. Per-field-blur save is the only reliable approach.

- Save partial entry to DB on each field blur (not on page unload)
- Associate partial entry with email field value when filled
- Mark as "Abandoned" status in entries list
- Trigger follow-up email 1 hour after abandonment (Pro email automation)

---

### 17 — Google Sheets — OAuth + Column Mapping

**Priority:** Low  
**Why:** Auto-append exists but column mapping is implicit. OAuth flow UX is undocumented.

- Build proper OAuth2 connect/disconnect UI in Settings
- Visual column mapping: form field → sheet column
- Delivery log and retry on Google API quota exceeded

---

### 18 — REST API

**Priority:** Low  
**Why:** Required for headless sites and modern block editor patterns. Not a free-tier priority — primarily used by developers and SaaS builders.

- `GET /wp-json/genform/v1/forms` — list forms (admin auth)
- `GET /wp-json/genform/v1/forms/{id}` — get form schema (admin auth)
- `POST /wp-json/genform/v1/forms/{id}/submit` — submit form (nonce)
- `GET /wp-json/genform/v1/entries` — list entries (admin auth)
- `GET /wp-json/genform/v1/entries/{id}` — entry detail (admin auth)
- `DELETE /wp-json/genform/v1/entries/{id}` — delete entry (admin auth)

---

*Completed features: [ROADMAP.md](ROADMAP.md)*
