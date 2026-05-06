# GenForm – Strategic Product Plan & Roadmap
**Version:** 1.0 | **Date:** 2026-05-06 | **Current Plugin Version:** 1.3.0

---

## EXECUTIVE SUMMARY

GenForm is a WordPress form builder with a mature technical foundation (PSR-4, custom DB tables, singleton architecture, Freemius-ready) and a well-structured Pro extension model. The free plugin is functionally complete for basic contact forms. The Pro plugin has 25+ features across fields, logic, payments, and integrations.

**The core strategic problem:** GenForm is currently underpositioned. The free tier is more capable than WPForms Lite but less discoverable. The Pro tier has the right features but no clear differentiation narrative. The product lacks the UX moments that drive activation, retention, and organic growth.

**The strategic opportunity:** Position GenForm as the honest middle ground — more powerful free tier than WPForms, cleaner UX than Fluent Forms, with a Pro tier that sells itself through automation and time-saving rather than artificial feature locks.

---

## PART 1: CURRENT PLUGIN ANALYSIS

### 1.1 Core Architecture

**Strengths:**
- Singleton pattern (`Core::instance()`) ensures clean initialization
- PSR-4 namespacing (`GenForm\`, `GenFormPro\`) with Composer autoloader
- Component-based Pro system — each Pro feature is an isolated class
- Custom DB tables (`wp_genform_forms`, `wp_genform_entries`) instead of postmeta — correct choice for query performance at scale
- Prepared SQL queries throughout — no SQL injection risk
- Elegant feature gating via `FeatureGate::has($feature)` — extensible and clean
- Post-submission hook architecture enables Pro integrations without core coupling
- Freemius SDK already vendored — licensing infrastructure is ready

**Weaknesses:**
- No REST API — only traditional `wp_ajax_*` endpoints (limits headless/Gutenberg/mobile use)
- No client-side (JS) validation — all validation is server-side AJAX round-trip
- Single-threaded email sending — no queue/retry for failed notifications
- No form versioning or revision history
- Detection_Helper for browser/OS parsing is low-value overhead

### 1.2 Current Feature Inventory

**Free (v1.3.0):**
- 12 field types: Text, Email, Textarea, Number, Select, Radio, Checkbox, Date, URL, Tel, Hidden, Password
- Form builder: drag-drop, field settings panel, tab system (Basic, Fields, Email, Settings)
- Submissions: AJAX, nonce, honeypot, reCAPTCHA v2, rate limiting (5/min), GDPR checkbox
- Email: admin notification + confirmation email with template tags
- Entries: WP_List_Table with starred/read/trash, bulk actions, CSV export (chunked 500-row streaming)
- Entry detail: full field view with browser/IP metadata
- Templates: 7 categories (General, Business, Booking, Feedback, Marketing, Education, Healthcare)
- Form status toggle (active/inactive)
- JSON import/export
- Gutenberg block + shortcode
- Dashboard widget (stats + recent entries)
- Global settings: reCAPTCHA keys, branding color, sender email

**Pro (v1.0.0):**
- 8 field types: File Upload, Page Break, Signature, Star Rating, Repeater, Address, Rich Text, Survey
- 5 logic modules: Conditional Logic, Multi-Step, Calculations, Save & Resume, Form Abandonment
- 3 payment gateways: Stripe, PayPal, Coupons
- 4 integrations: Webhook, Google Sheets, Slack, Mailchimp
- 6 features: User Registration, Post Submission, Entry Editor, PDF Generator, Geolocation, Landing Pages
- 2 additional DB tables: `wp_genform_files`, `wp_genform_payments`

### 1.3 What Is Missing

**Critical gaps (blockers for competitive parity):**
1. No client-side field validation (UX regression vs. every competitor)
2. No Akismet integration (spam filtering — industry standard)
3. No hCaptcha / Cloudflare Turnstile alternative to reCAPTCHA
4. No URL parameter prefill (populate fields from `?name=John`)
5. No multiple admin email notifications per form (even WPForms Basic allows one)
6. No REST API (required for modern block editor patterns and headless sites)
7. No form analytics / submission trend chart in dashboard
8. No guided onboarding (zero-to-first-form walkthrough)
9. No form-specific success redirect configuration visible in free UI

**UX gaps:**
1. ✅ ~~Forms list has no shortcode copy button~~ — shortcode copy button exists (gfm-shortcode-copy)
2. No "embed" wizard after saving a form
3. Builder has no undo/redo
4. ✅ ~~No inline field duplication in builder~~ — duplicateField() implemented in form-builder.js
5. Template preview is modal — no live preview

**Missing from Pro:**
1. No Zapier integration (highest-demand integration in category)
2. No ActiveCampaign / HubSpot / ConvertKit email marketing connectors
3. No form scheduling (open/close by date/time)
4. No entry limits (close form after N submissions)
5. No conditional confirmations
6. No submission email notification with file attachments
7. No webhook retry / delivery logs
8. No entry import (only export)
9. No A/B testing / split testing
10. No SMS notifications

### 1.4 What Is Poorly Implemented

| Area | Issue |
|---|---|
| Detection_Helper | Browser/OS detection on every submission is unnecessary overhead; data is low-value |
| Rate limiting | 5 submissions/minute transient is not per-form — a legitimate multi-form page would be blocked |
| Confirmation email | Only sends to the first email field found — brittle if form has multiple email fields |
| reCAPTCHA | Only v2 checkbox — v3 (invisible) is industry standard now |
| Email from name/address | Global only — no per-form override in free (creates spam filter risk) |
| Form builder tabs | "Settings" tab contains both styling AND behavior — cognitively overloaded |
| Entry metadata | Browser/OS stored as raw string, not structured JSON — hard to query/filter |
| Template library | 7 categories with unknown template counts — no search, no filter by field count |
| Admin email validation | Falls back to `admin_email` without user notification — silent failure |
| Dashboard widget | Object-cached but 5-min TTL may show stale counts after bulk deletes |

### 1.5 What Is Over-Engineered or Unnecessary

- `Detection_Helper` — adds HTTP parsing overhead for low-value browser/OS metadata
- Freemius SDK vendored but only partially integrated — creates dead code weight until fully activated
- `form_data` stores the full field schema as JSON in the DB — fine for reads, but makes DB-level field queries impossible (a limitation to address in v2 schema)

---

## PART 2: MARKET & COMPETITOR ANALYSIS (FREE TIERS ONLY)

### 2.1 WPForms Lite

**Free feature set:** AI form generator, unlimited forms, drag-drop builder, ~10 fields, reCAPTCHA/hCaptcha/Turnstile, 1 email notification, Stripe (3% fee), Constant Contact integration, template gallery (most locked).

**Critical free limitations:** No entry storage (email-only delivery), no conditional logic, no file upload, no CSV export, no multiple notifications, nearly all integrations locked.

**UX strength:** Guided "WPForms Challenge" walkthrough. Full-screen builder. Best onboarding in category.

**UX weakness:** Aggressively upsell-heavy. Persistent Pro banners. Locked field icons throughout builder create frustration.

**User trust signal:** Entry storage in cloud-only (Lite Connect) feels insecure to privacy-conscious users.

### 2.2 Fluent Forms Free

**Free feature set:** Conditional logic (fully free), conversational forms, multi-column layouts, form scheduling, keyword blocking, JSON import/export, URL param prefill, GET param support, 25+ fields, Stripe (1.9% fee), 12+ email marketing integrations (MailChimp, FluentCRM, Slack, Mautic, MailPoet, etc.), entry storage with CSV/Excel/JSON export, analytics dashboard (charts, heatmaps, top forms — added 2025).

**Critical free limitations:** File upload, phone field, multi-step forms, ratings, save & resume, quiz module, advanced styling, 50+ additional integrations, all locked in Pro.

**UX strength:** Cleanest free tier in the category. Non-aggressive upgrade prompts. Full analytics free.

**UX weakness:** Feature density can overwhelm beginners. Deeply integrated with WPFluent ecosystem (FluentCRM, FluentSMTP, FluentBooking) which creates lock-in optics.

**Market reality:** Fluent Forms free is the hardest competitor to beat head-to-head. It's generous to a fault, which compresses their own upgrade conversion.

### 2.3 Gravity Forms (Basic, $59/year)

**Paid baseline:** $59/year for 1 site. No free tier.

**What $59 gets:** 38+ fields, conditional logic, calculations, 30+ email integrations (MailChimp, HubSpot, ActiveCampaign, etc.), CSV export, unlimited notifications, entry storage, developer-grade hook system.

**Critical $59 limitations:** No payments (requires Pro $159), no Zapier/webhooks, no analytics dashboard natively (requires GravityKit add-ons), no landing pages, no user registration.

**UX weakness:** No onboarding wizard. Steeper learning curve. Operates inside standard WP admin (no full-screen builder). No AI form generation. Analytics require expensive third-party add-ons.

**Market reality:** Gravity Forms is the "developer's choice" and "agency standard." It wins on ecosystem depth and reputation, not on UX or free tier value.

### 2.4 Extracted Competitive Intelligence

**Industry baseline (minimum free value users expect in 2025-2026):**
- Unlimited forms ✓
- All submissions stored in WP database ✓
- At least 12 core field types ✓
- Admin email notification ✓
- Confirmation message or redirect ✓
- Basic spam protection (honeypot + CAPTCHA) ✓
- CSV export of entries ✓
- Gutenberg block ✓
- Shortcode support ✓

**Differentiators that drive preference:**
- Conditional logic in free (Fluent Forms does this; WPForms does not)
- Onboarding/first-success moment (WPForms does this best)
- Analytics dashboard (Fluent Forms, 2025)
- Clean, non-aggressive upsell UX (Fluent Forms)
- AI form generation (WPForms, Fluent Forms)
- Payments free with transaction fee (WPForms, Fluent Forms)

**What users pay for (Pro upgrade motivators):**
- File uploads (universal Pro gate)
- Multi-step forms
- Advanced integrations (Zapier, CRM, Google Sheets)
- Conditional email routing
- Payment processing without transaction fees
- User registration from form
- PDF generation
- Save & Resume
- Priority support

---

## PART 3: USER DEMAND ANALYSIS

### 3.1 Primary User Segments

**Segment A — The Freelancer/Small Business Owner (60% of free installs)**
- Builds 1-5 forms: contact, quote request, newsletter signup
- Needs: quick setup, email delivery, basic spam protection, entries in WP dashboard
- Will pay for: file uploads (portfolio submissions), integrations (Mailchimp for newsletter), PDF (invoice)
- Won't pay for: advanced logic, payments, CRM sync

**Segment B — The Agency Developer (25% of installs)**
- Builds forms for multiple client sites
- Needs: reliable core, JSON import/export (reuse across sites), dev-friendly hooks, multisite support
- Will pay for: white-labeling, advanced logic, Zapier/webhook, client-facing entry management
- Won't pay for: basic contact form features

**Segment C — The SaaS/App Builder (15% of installs)**
- Uses forms for: user onboarding, lead capture, payments, surveys
- Needs: REST API, headless support, conditional logic, calculations, user registration
- Will pay for: everything Pro, especially payments + user registration + conditional logic

### 3.2 Activation Path (First Success Moment)

**Current state:** User installs → lands on empty forms list → no guidance → must discover builder on their own. Zero activation engineering.

**Required activation sequence:**
1. Install → Welcome screen with "Create your first form" CTA
2. 3-step guided wizard: (1) choose a template, (2) configure email notification, (3) embed on a page
3. First form live → first submission received → "You got your first submission!" toast
4. 7-day email drip (via Freemius) surfacing the next feature they haven't used

**Retention triggers (free → sticky):**
- Entry notifications arriving in inbox (daily habit formation)
- Entry management dashboard (makes WP admin the "hub" for business data)
- Template library (reason to return when starting new projects)
- Form analytics (gives users a reason to log in and check performance)

**Upgrade triggers (free → Pro):**
- "Upgrade to upload files" prompt when a client asks for a document submission form
- "Remove transaction fee" prompt after first Stripe payment (if free Stripe is included)
- "Connect to Mailchimp" prompt when user sets up newsletter form
- "Add conditional logic" prompt in builder when user has 5+ fields on a form
- Entry volume threshold ("You have 500 entries — unlock advanced filtering and PDF export")

---

## PART 4: GAP ANALYSIS — GENFORM VS MARKET

### 4.1 Feature Gaps vs. Competitors

| Feature | WPForms Lite | Fluent Forms | GenForm 1.3.0 | Gap Level |
|---|---|---|---|---|
| Entry storage in WP DB | No (cloud only) | Yes | Yes | ✅ No gap |
| Client-side validation | Yes | Yes | **No** | 🔴 Critical |
| URL param prefill | No | Yes | **No** | 🟡 Important |
| Akismet spam filter | Yes | Yes | **No** | 🟡 Important |
| hCaptcha / Turnstile | Yes | Yes | **No** | 🟡 Important |
| Multiple email notifications | No (1 only) | Yes | **No (1 only)** | 🟡 Important |
| Conditional logic (free) | No | Yes | **No (Pro)** | 🟠 Strategic |
| Analytics/submission chart | No | Yes (2025) | **No** | 🟡 Important |
| Form scheduling | No | Yes | **No (Pro implied)** | 🟡 Important |
| Onboarding wizard | Yes (excellent) | Partial | **No** | 🔴 Critical |
| Shortcode copy button | Yes | Yes | **Yes ✅** | ✅ No gap |
| AI form generator | Yes | Yes | **No** | 🟠 Strategic |
| Embed wizard | Yes | No | **No** | 🟡 Important |
| Conditional confirmations | No | Pro | **No (Pro implied)** | 🟠 Strategic |
| REST API | No | No | **No** | 🟡 Important |
| Form entry limits | No | Pro | **No** | 🟠 Strategic |

### 4.2 Dashboard Issues

| Issue | Severity |
|---|---|
| No analytics or submission trend on forms list | High |
| ✅ ~~No shortcode copy button on forms list~~ — implemented | — |
| No "embed" flow after form creation | High |
| ✅ ~~Entry table shows no form-level summary stats~~ — submission count column exists | — |
| Settings page has no section for "Notifications" separate from "Global" | Medium |
| ✅ ~~No global entry search across all forms~~ — entry search via LIKE query exists | — |

### 4.3 UX Flow Weaknesses

1. **Form creation → publish gap is unbridged.** User saves form but has no guidance on embedding it.
2. **Builder feedback is partial.** No undo/redo. ✅ Field duplication exists (`duplicateField()`). No live preview mode.
3. **Email notification configuration is buried.** Must click "Email" tab; no visual template editor.
4. **Entry detail is read-only.** Pro feature (Entry Editor) should gate *editing*, but viewing is already available.
5. ✅ **Empty state design exists** in forms-list.php — SVG illustration + CTA button already implemented.

### 4.4 Performance Risks

| Risk | Severity |
|---|---|
| `entry_data` stored as JSON longtext — full table scan for field-level queries | Medium (current scale OK, v2 schema needed at scale) |
| No pagination on entries AJAX loads | Medium |
| Detection_Helper parses User-Agent on every submission | Low |
| Email sends synchronously during AJAX submission (blocks response if SMTP is slow) | High |
| No webhook retry or dead-letter queue in Pro | High |

---

## PART 5: FREE VS PRO STRATEGY

### 5.1 Core Principle

**Free must work.** A user should be able to build a real contact form, receive submissions, manage entries, and export data — with zero friction and zero artificial blocks. The free tier builds trust.

**Pro must save time and add business value.** Pro features should feel like "I can't imagine running my workflow without this." They should automate manual steps, connect to business tools, and handle business-critical operations (payments, compliance, file storage).

**Never gate by quantity (forms, entries, fields) in free.** Quantity limits are the most user-hostile form of limitation and generate the most negative reviews. Gate by feature, not volume.

### 5.2 Free Must Include

- Unlimited forms, unlimited entries, unlimited fields
- All 12 current field types + Phone field (industry baseline)
- Drag-drop builder with undo/redo
- Admin email notification (1 per form, customizable)
- User confirmation email (1 per form)
- Entry storage + CSV export
- Basic spam protection (honeypot + reCAPTCHA v2/v3 + hCaptcha + Turnstile)
- Akismet integration
- URL parameter prefill
- Gutenberg block + shortcode
- Template library (all general/contact/feedback templates)
- Form status toggle (active/inactive)
- JSON import/export
- Client-side field validation
- Basic dashboard analytics (total submissions per form, last 30 days trend)
- Shortcode copy button + embed wizard
- Onboarding wizard (first-time activation)

### 5.3 Pro Must Include

**Automation (the #1 upgrade motivator):**
- Multiple email notifications per form
- Conditional email routing (send to different recipients based on field values)
- Zapier/Make webhook integration
- Webhook with retry logic and delivery logs
- Mailchimp, ConvertKit, ActiveCampaign, HubSpot list sync
- Slack channel notifications
- Google Sheets auto-append

**Advanced form capabilities:**
- Conditional logic (show/hide fields)
- Multi-step forms with progress bar
- File upload with validation, secure storage, size limits
- Form scheduling (open/close by date)
- Entry count limits (close after N submissions)
- Save & Resume
- Form abandonment capture
- Calculations (quote builders, pricing calculators)

**Business operations:**
- Stripe payments (no transaction fee)
- PayPal payments
- Coupon codes
- PDF generation (invoices, certificates, confirmations)
- User registration from form
- Post/CPT creation from form
- Entry editing in admin
- Geolocation data on entries

**Advanced fields:**
- Signature
- Star Rating
- Survey / NPS / Likert Scale
- Repeater rows
- Address with autocomplete
- Rich Text (WYSIWYG)
- Phone with country code

**Reporting:**
- Visual submission charts
- Conversion rate per form
- Field-level drop-off analysis
- Entry heatmap (day/hour pattern)

**Developer/Agency:**
- REST API endpoints
- White-label / rebrand
- Landing pages (distraction-free form URLs)
- Multisite network support
- Entry import

### 5.4 Split Features (Strategic Free-Tease → Pro-Convert)

| Feature | Free Version | Pro Version |
|---|---|---|
| Spam protection | Honeypot + reCAPTCHA | + Akismet + Turnstile + keyword blocking |
| Email notifications | 1 admin + 1 confirmation | Unlimited + conditional routing |
| Entry management | View + star + trash + CSV | + Edit + bulk export formats + import |
| Analytics | Basic (count + 30-day trend) | Full charts + heatmaps + conversion |
| Templates | General/Contact/Feedback | All 7 categories + Pro-exclusive templates |
| Conditional logic | None | Full (show/hide/skip/branch) |

---

## PART 6: FULL ROADMAP DOCUMENT

---

### 🟢 PART A: FREE CORE

---

#### 🔹 CHAPTER F01: FORM BUILDER CORE

---

**[F01.1] Drag-Drop Field Builder**

Status: Existing — Partially Complete  
Current Behavior: Fields can be dragged from sidebar into canvas. Click to edit in settings panel. Field duplication implemented via `duplicateField()` (form-builder.js lines 222–242).  
Issue/Gap: No undo/redo. No keyboard accessibility. No live preview mode. No field search in sidebar.  
Completed: ✅ Field duplication (`duplicateField()` — deep-clones config, assigns new ID)  
Required Action:  
- Implement undo/redo stack (client-side, last 20 actions)  
- Add field search/filter in the left sidebar  
- Add keyboard shortcut: Delete key removes selected field, Ctrl+D duplicates  
Free vs Pro Decision: Keep (Free)  
Reason: Builder UX is a baseline expectation. WPForms and Fluent Forms both have duplication and undo. Without undo/redo, the builder still feels incomplete.

---

**[F01.2] Field Types — Free Tier**

Status: Existing — Partially Complete  
Current Behavior: 12 types: Text, Email, Textarea, Number, Select, Radio, Checkbox, Date, URL, Tel, Hidden, Password  
Completed: ✅ Tel/phone field (`type="tel"`) is already in the free tier (form-template.php) — not gated in Pro Feature_Gate  
Issue/Gap: No dedicated "Phone" field with phone-specific formatting/validation (tel is basic text with type="tel"). No "Divider/Section Break" for visual form organization.  
Required Action:  
- Add phone-specific regex validation and formatting to the existing tel field (rename display to "Phone" with phone icon)  
- Add Section Break/Divider field (label + description, no data capture) as free  
- Keep advanced phone (country code selector) and all other advanced field types (Signature, Rating, Repeater, etc.) in Pro  
Free vs Pro Decision: Keep/Split — Tel/Phone + Section Break to Free; country-code Phone selector stays Pro  
Reason: Tel exists but lacks phone-specific UX. Section Break has zero business value in Pro — it only organizes the form layout.

---

**[F01.3] Field Settings Panel**

Status: Existing — Good  
Current Behavior: Click field → right panel shows label, placeholder, required, width, CSS class, help text, default value, type-specific options.  
Issue/Gap: "Settings" tab in builder is cognitively overloaded — mixes styling (font size, submit alignment) with behavior (success message, redirect, GDPR). Should be split into distinct tabs.  
Required Action:  
- Rename/restructure builder tabs: "Fields" | "Notifications" | "Confirmation" | "Design" | "Security"  
- Move styling options to "Design" tab  
- Move GDPR to "Security" tab  
Free vs Pro Decision: Keep (Free)  
Reason: Tab restructuring improves discoverability and mirrors how competitors organize settings.

---

**[F01.4] Form JSON Import/Export**

Status: Existing — Good  
Current Behavior: Import/export form schema as JSON via admin AJAX.  
Issue/Gap: No validation feedback on malformed JSON import. No "overwrite vs. create new" option on import.  
Required Action:  
- Add client-side JSON schema validation before upload  
- Add import mode selector: "Create new form" vs "Replace existing form"  
Free vs Pro Decision: Keep (Free)  
Reason: JSON portability is a developer-trust feature. Keeping it free signals good faith to the agency segment.

---

**[F01.5] Form Duplication**

Status: Existing — Good  
Current Behavior: "Duplicate" option on forms list duplicates form and settings.  
Issue/Gap: No confirmation dialog. No way to rename on duplicate.  
Required Action: Add rename prompt in duplication flow.  
Free vs Pro Decision: Keep (Free)  
Reason: Baseline productivity feature for all users.

---

**[F01.6] URL Parameter Prefill**

Status: Missing  
Current Behavior: No support for populating form fields from URL query parameters.  
Issue/Gap: This is a standard feature in Fluent Forms (free) and Gravity Forms. Marketing teams and developers depend on it for UTM → form → CRM flows. Without it, GenForm cannot be used in email campaigns or ad landing page flows.  
Required Action:  
- In field settings, add "Parameter Name" input (e.g., `name`, `email`)  
- In `frontend.js`, on DOM ready, parse `window.location.search` and populate matching fields  
- Sanitize values (strip HTML, limit length)  
Free vs Pro Decision: Keep (Free)  
Reason: URL prefill is a trust-building feature that makes forms useful in real marketing flows. It costs nothing to implement and removes a major competitive gap.

---

**[F01.7] Undo/Redo in Builder**

Status: Missing  
Current Behavior: No undo/redo. Changes are permanent until page reload.  
Issue/Gap: Every field movement or deletion is irreversible in current session. This creates fear of experimentation.  
Required Action: Implement client-side command history stack (max 30 states) using JavaScript. Track add, remove, reorder, update operations.  
Free vs Pro Decision: Keep (Free)  
Reason: Builder confidence is a prerequisite for form creation activation. Users who fear accidental deletion abandon builders.

---

#### 🔹 CHAPTER F02: SUBMISSION HANDLING

---

**[F02.1] Client-Side Field Validation**

Status: Missing — Critical  
Current Behavior: Validation happens server-side via AJAX round-trip. Required fields, email format, min/max length are only enforced after submission.  
Issue/Gap: Every competitor validates inline on blur/change. Without client-side validation, users complete the entire form before seeing errors — highest source of form abandonment.  
Required Action:  
- Implement inline validation in `frontend.js`: validate on field blur + on submit  
- Validate: required, email format, URL format, min/max length, min/max number, pattern (regex for tel/hidden)  
- Show error message below field with ARIA attributes for accessibility  
- Server-side validation remains as security fallback  
Free vs Pro Decision: Keep (Free)  
Reason: Client-side validation is a UX baseline. Its absence is a dealbreaker for serious users and generates the most negative user feedback in form builders.

---

**[F02.2] Spam Protection — reCAPTCHA v3**

Status: Needs Improvement (v2 only currently)  
Current Behavior: reCAPTCHA v2 checkbox. Requires user interaction. Configured globally.  
Issue/Gap: v2 checkbox is intrusive and creates friction. v3 (invisible scoring) is the 2025 standard. Users also want alternatives: hCaptcha (privacy-first), Cloudflare Turnstile (frictionless).  
Required Action:  
- Add reCAPTCHA v3 (invisible) support alongside v2  
- Add hCaptcha as alternative  
- Add Cloudflare Turnstile as alternative  
- Allow per-form CAPTCHA type selection  
Free vs Pro Decision: Keep (Free)  
Reason: All three competitors include all CAPTCHA variants free. Spam protection is not a monetization vector — it's a trust vector.

---

**[F02.3] Akismet Spam Filtering**

Status: Missing  
Current Behavior: Honeypot + reCAPTCHA only.  
Issue/Gap: Akismet is already installed on most WordPress sites. Not integrating with it is a missed opportunity. Sophisticated spam bots bypass CAPTCHA — Akismet's content-based filtering catches these.  
Required Action:  
- Check if Akismet is active; if so, offer toggle in form Security settings  
- On submission, call `akismet_verify_key()` and `akismet_comment_check()` with form data as "comment" context  
- Mark entries flagged by Akismet with a "spam" status; exclude from default entry view  
Free vs Pro Decision: Keep (Free)  
Reason: WPForms, Fluent Forms, and Gravity Forms all include Akismet free. Absence is a clear gap.

---

**[F02.4] Rate Limiting — Per-Form**

Status: Needs Improvement  
Current Behavior: Rate limit is 5 submissions/minute per IP — global across all forms.  
Issue/Gap: A legitimate user on a page with multiple forms (contact + newsletter + feedback) could trigger the rate limit by submitting two different forms. Rate limiting should be per-form, per-IP.  
Required Action: Change transient key from `genform_rate_{ip}` to `genform_rate_{form_id}_{ip}`.  
Free vs Pro Decision: Keep (Free)  
Reason: This is a correctness fix, not a feature. Current behavior can incorrectly block legitimate users.

---

**[F02.5] Form Entry Limits (Close After N Submissions)**

Status: Missing  
Current Behavior: Forms accept submissions indefinitely.  
Issue/Gap: Event registrations, survey campaigns, and giveaways require closing forms after a set number of entries. Without this, users must manually monitor and deactivate.  
Required Action:  
- Add "Entry Limit" field in form Settings  
- On submission, count entries for form; if limit reached, return configured "closed" message  
- Show limit and current count in forms list admin  
Free vs Pro Decision: Pro  
Reason: Form limits are a business workflow feature used by event managers and marketers — a clear Pro use case with direct time-saving value.

---

**[F02.6] Form Scheduling (Date-Based Open/Close)**

Status: Missing  
Current Behavior: Manual active/inactive toggle only.  
Issue/Gap: Users running time-limited campaigns (holiday promotions, event registrations) must manually remember to activate and deactivate forms at specific times.  
Required Action:  
- Add "Schedule" section in form Settings: start date/time, end date/time  
- Show scheduled status in forms list  
- Display custom "not yet open" and "now closed" messages on frontend  
Free vs Pro Decision: Pro  
Reason: Scheduling is an automation feature that saves manual intervention — clear Pro value proposition.

---

#### 🔹 CHAPTER F03: NOTIFICATIONS & EMAIL

---

**[F03.1] Admin Notification Email**

Status: Existing — Needs Improvement  
Current Behavior: 1 admin notification per form. Global from/sender settings with per-form override. Template tags for field values.  
Issue/Gap: From email is global-first, form-second — SMTP configuration often requires a fixed sender. No way to add CC/BCC recipients. No conditional notification (send to Manager A if Department = Sales, Manager B if Department = Support).  
Required Action:  
- Add CC/BCC fields to notification email config (Free — single notification)  
- Add Reply-To field (already partially implemented, confirm it's surfaced in UI)  
- Keep multiple notifications + conditional routing in Pro  
Free vs Pro Decision: Split — CC/BCC/Reply-To in Free; Multiple notifications + conditional routing in Pro  
Reason: CC/BCC is a basic need for small teams. Conditional routing requires conditional logic awareness — a legitimate Pro feature.

---

**[F03.2] Multiple Notifications Per Form**

Status: Missing (Free limitation)  
Current Behavior: 1 admin notification only.  
Issue/Gap: Any form serving multiple departments or roles needs multiple notifications. A job application form needs to notify HR and the hiring manager. This is where WPForms forces upgrades too.  
Required Action: Pro feature — allow creating multiple named notification rules per form, each with independent recipients, subject, body, and conditional send rules.  
Free vs Pro Decision: Pro  
Reason: Multiple notifications is a team/business workflow feature. Single notification covers 80% of use cases for free users.

---

**[F03.3] Confirmation Email to Submitter**

Status: ✅ Implemented — Field selector exists  
Current Behavior: Confirmation email uses `gfm_conf_to_field` setting to determine the recipient email field. `Email::sendConfirmation()` reads this setting (Email.php lines 97, 115–116). form-builder.js (line 97) loads the setting into the builder UI.  
Issue/Gap: None for the field selector mechanism. Verify the dropdown lists all email-type fields clearly in the builder "Email" tab UI.  
Required Action: QA pass — confirm the "Send To" dropdown in the builder shows all email-type fields from the current form schema. No code changes needed unless the UI is missing this control visually.  
Free vs Pro Decision: Keep (Free)  
Reason: Already implemented correctly — maintain as-is.

---

**[F03.4] Async/Queued Email Sending**

Status: Missing  
Current Behavior: Emails are sent synchronously during the AJAX submission handler. If SMTP is slow or times out, the user sees a delayed or failed response.  
Issue/Gap: On high-traffic forms or when using external SMTP (SendGrid, Mailgun), synchronous email blocks the submission response for 2-5 seconds. Can cause timeouts.  
Required Action: Use WordPress's `wp_schedule_single_event()` to queue email sending as a background cron job. Return success to user immediately after entry is saved.  
Free vs Pro Decision: Keep (Free)  
Reason: This is a reliability fix, not a feature. Synchronous email is a architectural weakness.

---

#### 🔹 CHAPTER F04: ENTRY MANAGEMENT

---

**[F04.1] Entries List Table**

Status: Existing — Partially Complete  
Current Behavior: WP_List_Table with starred/read/trash, filter by form and status, bulk actions, CSV export.  
Completed: ✅ Entry search implemented — Entries_Table.php searches `e.entry_data LIKE %s` (lines 69–72)  
Issue/Gap: No date range filter. No column selector (choose which fields to show as columns).  
Required Action:  
- Add date range filter (From / To date pickers) against `e.created_at`  
- Keep column selector as Pro feature  
Free vs Pro Decision: Split  
Reason: Date filter is a basic data management need. Column selection is a power-user feature suitable for Pro.

---

**[F04.2] CSV Export**

Status: Existing — Good  
Current Behavior: Streaming CSV export with 500-row chunking, UTF-8 BOM.  
Issue/Gap: No date range filter on export. No field selection (exports all fields). No Excel (.xlsx) or JSON export.  
Required Action:  
- Add date range parameters to CSV export  
- Add Excel and JSON export formats as Pro features  
Free vs Pro Decision: Split — CSV (free), Excel/JSON export (Pro)  
Reason: CSV is the industry baseline. Excel/JSON serve agency and developer workflows — clear Pro value.

---

**[F04.3] Entry Detail View**

Status: Existing — Good  
Current Behavior: Full field-by-field view with browser/IP/OS metadata, starred/read status.  
Issue/Gap: Entry is read-only (editing is a Pro feature — correct). However, navigation between entries (prev/next) is missing — user must return to list.  
Required Action: Add Prev/Next entry navigation links in entry detail view.  
Free vs Pro Decision: Keep (Free)  
Reason: Prev/Next navigation is a basic usability feature, not a monetization opportunity.

---

**[F04.4] Entry Editor (Edit Entry Data)**

Status: Pro — Correctly gated  
Current Behavior: Entries are read-only in free.  
Issue/Gap: None — this is correctly positioned in Pro.  
Required Action: Ensure editing is accessible and well-designed in Pro. Support field-level edit with re-validation.  
Free vs Pro Decision: Pro  
Reason: Entry editing is used by admins managing data quality — a business operations feature.

---

#### 🔹 CHAPTER F05: DASHBOARD & SETTINGS

---

**[F05.1] Forms List Page**

Status: Exists — Partially Complete  
Current Behavior: Table with form name, status toggle, edit/duplicate/delete actions, form preview.  
Completed:  
- ✅ Submission count column — fetched as `e_c`, displayed as clickable badge (forms-list.php lines 102–104)  
- ✅ Shortcode copy button — `gfm-shortcode-copy` div with dashicons-admin-page icon (forms-list.php lines 96–99)  
- ✅ Empty state design — SVG illustration + "Create your first form" CTA (forms-list.php lines 60–72)  
Issue/Gap: No "Last Submission" date column. No "Embed" quick action/modal after form save.  
Required Action:  
- Add "Last Submission" column (MAX(created_at) from wp_genform_entries GROUP BY form_id — cache with form counts)  
- Add "Embed" quick action button that shows a modal with shortcode + block embed instructions  
Free vs Pro Decision: Keep (Free)  
Reason: Last submission date aids quick form health check. Embed modal bridges the creation-to-publish gap.

---

**[F05.2] Submission Analytics Dashboard**

Status: Missing  
Current Behavior: Dashboard widget shows total forms/entries + 5 recent entries.  
Issue/Gap: Fluent Forms (free since 2025) now includes a full analytics dashboard with charts, top forms, heatmaps. GenForm has nothing comparable. This is becoming a competitive baseline feature.  
Required Action:  
- Add basic analytics tab in GenForm admin: total submissions (all time + last 30 days), submissions per form (bar chart), submissions over time (line chart, last 30 days)  
- Use Chart.js (already common in WP plugins) for rendering  
- Keep advanced analytics (heatmaps, conversion rates, field drop-off) in Pro  
Free vs Pro Decision: Split — Basic chart in Free; Advanced analytics in Pro  
Reason: Basic submission trends are a retention feature — gives users a reason to return to the dashboard. Advanced analytics justify Pro pricing.

---

**[F05.3] Settings Page**

Status: Exists — Needs Improvement  
Current Behavior: Single settings page with reCAPTCHA keys, branding color, sender email.  
Issue/Gap: No organized sections. No indication of what each setting does. No test email button. No "Save and Test" workflow for reCAPTCHA.  
Required Action:  
- Reorganize settings into tabs: "General" | "Email" | "Spam Protection" | "Appearance"  
- Add "Send Test Email" button in Email settings  
- Add reCAPTCHA connection test / validation  
Free vs Pro Decision: Keep (Free)  
Reason: Settings UX directly affects setup success and first-run experience.

---

**[F05.4] Onboarding Wizard**

Status: Missing — Critical  
Current Behavior: Plugin activates → user lands on empty forms list. No guidance.  
Issue/Gap: WPForms "Challenge" wizard is industry-leading. Users who complete an onboarding flow have 3-5x higher 30-day retention than those who don't (standard SaaS metric). Without onboarding, GenForm bleeds installs.  
Required Action:  
- On first activation, show a 3-step overlay wizard:  
  Step 1: "Create your first form" — template chooser  
  Step 2: "Set your notification email" — pre-filled with admin email  
  Step 3: "Embed your form" — show shortcode + Gutenberg instruction  
- Track wizard completion in `wp_options` as `genform_onboarding_complete`  
- Allow wizard to be dismissed and relaunched from Settings  
Free vs Pro Decision: Keep (Free)  
Reason: Onboarding is the single highest-ROI UX investment. First-success moment drives retention, word-of-mouth, and review posting.

---

#### 🔹 CHAPTER F06: UI / UX / BLOCKS

---

**[F06.1] Gutenberg Block**

Status: Existing — Functional but Thin  
Current Behavior: Block with form selector. Server-side render delegates to shortcode. Requires WP 6.0+.  
Issue/Gap: No block preview (shows form in editor). No block-level styling controls. Block has no "Create new form" flow — user must pre-create a form before using the block.  
Required Action:  
- Add block preview using iframe or server-side render in editor  
- Add "Create new form" button inside the block when no form is selected  
- Add basic block alignment control (wide/full)  
Free vs Pro Decision: Keep (Free)  
Reason: Block editor is the primary WordPress content interface since 5.0. A thin block damages perception.

---

**[F06.2] Frontend Form Rendering**

Status: Existing — Mostly Complete  
Current Behavior: Dynamic field rendering with CSS variables for font/color customization. Responsive.  
Completed:  
- ✅ Form loading state — submit button disabled + spinner shown during AJAX (frontend.js lines 59–66): `btn.disabled = true; btn.innerHTML = '<span class="gfm-spinner"></span> ...'`  
- ✅ Success/error message animation — `@keyframes gfm-fade-in` with opacity + translateY applied to `.gfm-message` (frontend.css)  
Issue/Gap: No dark mode support.  
Required Action:  
- Add `@media (prefers-color-scheme: dark)` block to `frontend.css` overriding background, border, text, and input colors  
Free vs Pro Decision: Keep (Free)  
Reason: Loading state and animations are done. Only dark mode CSS remains — a modern expectation with minimal implementation cost.

---

**[F06.3] Form Styling System**

Status: Existing — Needs Improvement  
Current Behavior: CSS variables for primary color, font size, font weight. Global.  
Issue/Gap: No per-form style override without custom CSS. No theme compatibility check. No "Reset to defaults" option.  
Required Action:  
- Add per-form color override in "Design" tab (inherits global by default)  
- Add theme-compatibility mode toggle (minimal CSS, inherits theme styles)  
Free vs Pro Decision: Split — basic per-form color in Free; Advanced styling (custom CSS, typography, spacing, animation) in Pro  
Reason: Basic branding control is a table-stakes free feature. Advanced design is a Pro differentiator for agencies.

---

**[F06.4] Template Library**

Status: Existing — Needs Improvement  
Current Behavior: 7 categories, modal-based template selection, no search.  
Issue/Gap: No template search. No template preview (only description). No template ratings or "most used" sort. Business/Education/Healthcare templates may contain Pro-only fields — importing them creates confusion.  
Required Action:  
- Add template search by name/keyword  
- Add template preview (rendered screenshot or live iframe)  
- Label templates that require Pro fields with a "Requires Pro" badge  
- Add "blank form" shortcut more prominently  
Free vs Pro Decision: Split — all templates visible, Pro-required ones labeled; Pro gets exclusive "premium" template designs  
Reason: Template discovery is an activation accelerator. Search is a baseline UX expectation.

---

### 🟠 PART B: PRO FEATURES

---

#### 🔹 CHAPTER P01: ADVANCED FIELDS

---

**[P01.1] File Upload**

Status: Pro — Correctly gated  
Current Behavior: Drag-drop file upload with validation, secure storage, size limits. Stores in `wp_genform_files`.  
Issue/Gap: No post-upload virus scan hook. No image preview for image uploads. No file count limit per submission.  
Required Action:  
- Add max file count setting per field  
- Add image type → thumbnail preview after upload  
- Expose `genform_file_uploaded` action hook for third-party virus scanning  
Free vs Pro Decision: Pro  
Reason: File uploads require server storage, security considerations, and admin management — clear Pro value. Universal across all competitors as a Pro gate.

---

**[P01.2] Signature Field**

Status: Pro — Correctly gated  
Current Behavior: Touch/mouse canvas signature capture.  
Issue/Gap: No signature export to PNG/PDF. No signature verification (timestamps).  
Required Action: Add signature export as embedded PNG in PDF generation (P04.4). Add timestamp + IP metadata stored with signature.  
Free vs Pro Decision: Pro  
Reason: Signatures are used in legal and compliance workflows — high business value, clear Pro positioning.

---

**[P01.3] Phone Field with Country Code**

Status: Partially misplaced — Currently Pro  
Current Behavior: Phone field is in Pro.  
Issue/Gap: Phone is an industry-baseline field. Every competitor (including WPForms Lite) includes a basic phone field free. The Tel field type exists in free but lacks phone-specific formatting/validation.  
Required Action:  
- Move basic Phone field (text input + phone validation) to Free  
- Keep Phone + country code selector (intl-tel-input library) in Pro  
Free vs Pro Decision: Split — Basic phone field in Free; Country code selector in Pro  
Reason: Locking a basic phone field in Pro creates immediate negative perception. Country code selector is a legitimate Pro feature for international forms.

---

**[P01.4] Address with Autocomplete**

Status: Pro — Correctly gated  
Current Behavior: Multi-subfield address (street, city, state, zip, country).  
Issue/Gap: No Google Places autocomplete. No geocoding (convert address to lat/lng). Country list not localized.  
Required Action:  
- Add Google Places API autocomplete option  
- Add Mapbox as alternative autocomplete provider  
- Store geocoded lat/lng in entry metadata (connects to Geolocation feature)  
Free vs Pro Decision: Pro  
Reason: Address with autocomplete requires API key management and external service — appropriate Pro gate.

---

**[P01.5] Survey / NPS / Likert Scale**

Status: Pro — Correctly gated  
Current Behavior: Survey field type exists.  
Issue/Gap: No NPS (Net Promoter Score) analysis in reporting. No Likert scale aggregate charts. Results are stored as individual entries, not aggregated.  
Required Action:  
- Add NPS score aggregation in Pro analytics  
- Add Likert response distribution chart  
- Add survey-specific export (responses by question, not by entry)  
Free vs Pro Decision: Pro  
Reason: Survey analytics require specialized reporting — clear Pro value add that justifies the field being Pro-only.

---

#### 🔹 CHAPTER P02: LOGIC & INTELLIGENCE

---

**[P02.1] Conditional Logic Engine**

Status: Pro — Partially Implemented ⚡  
Current Behavior: Field-level conditional logic (`show|hide`, `all|any`, with 9 operators).  
Completed: ✅ Visual conditional logic panel exists — `renderConditionalPanel()` in Conditional_Engine.php (lines 51–99) renders checkboxes, action/logic/operator dropdowns, and "Add Rule" button inside the builder.  
Issue/Gap: No conditional page navigation in multi-step. No conditional submission redirect (redirect to URL A if condition X, URL B if condition Y). No conditional confirmation messages.  
Required Action:  
- Add conditional redirect as form-level logic (separate from field-level show/hide)  
- Add conditional confirmation messages (show different thank-you text based on field values)  
- Connect conditional engine to multi-step page navigation (skip pages based on rules)  
Free vs Pro Decision: Pro  
Reason: The core UI and engine exist. Remaining work is extending to form-level outcomes (redirect, confirmation) and multi-step integration.

---

**[P02.2] Multi-Step Forms**

Status: Pro — Correctly gated  
Current Behavior: Page Break field triggers multi-step UI with progress bar.  
Issue/Gap: No step validation before advancing. No step-level conditional skip. No animated transitions between steps. No "go back" without losing step data.  
Required Action:  
- Enforce field validation on "Next" click before advancing  
- Preserve all step data in JS memory (not lost on back navigation)  
- Add configurable progress bar styles (steps, percentage, bar)  
- Add conditional step skip (skip Step 3 if answer to field in Step 2 is X)  
Free vs Pro Decision: Pro  
Reason: Multi-step forms are used for complex data collection (job applications, surveys, onboarding). They are Pro-standard across all competitors.

---

**[P02.3] Calculations Engine**

Status: Pro — Correctly gated  
Current Behavior: Mathematical formula support for pricing calculators.  
Issue/Gap: No formula editor UI (currently likely text input). No testing/preview of formula output. No currency formatting of results.  
Required Action:  
- Add visual formula builder with field references, operators, and function library  
- Add real-time formula preview in builder  
- Add number formatting (currency, percentage, decimal places) for calculated fields  
Free vs Pro Decision: Pro  
Reason: Calculations enable quote builders, pricing tools, and ROI calculators — high business value applications.

---

**[P02.4] Save & Resume**

Status: Pro — Correctly gated  
Current Behavior: Pause form and return with a link.  
Issue/Gap: No session-based partial save for non-registered users. No expiry on resume link. No admin visibility into abandoned saves.  
Required Action:  
- Store partial entries in `wp_genform_entries` with status `draft`  
- Generate time-limited resume URL (72-hour expiry)  
- Show draft entries in admin with "Incomplete" badge  
- Clean up expired drafts via WP Cron  
Free vs Pro Decision: Pro  
Reason: Save & Resume is used in long application forms — clear Pro use case.

---

**[P02.5] Form Abandonment Capture**

Status: Pro — Correctly gated  
Current Behavior: Capture partial entries before form submission.  
Issue/Gap: Implementation details unclear — need to verify whether it saves on every field blur or on page unload. Page unload capture is unreliable in modern browsers (no `beforeunload` guarantee).  
Required Action:  
- Save partial entry to DB on each field blur (reliable)  
- Associate partial entry with email field value if filled (for follow-up)  
- Mark as "Abandoned" status in entries  
- Connect to email notification (send follow-up email 1 hour after abandonment — Pro automation)  
Free vs Pro Decision: Pro  
Reason: Abandonment recovery is a sales/marketing automation feature with direct revenue impact.

---

#### 🔹 CHAPTER P03: INTEGRATIONS

---

**[P03.1] Zapier / Make (Webhook)**

Status: Pro — Needs Expansion  
Current Behavior: Webhook integration sends POST with entry data.  
Issue/Gap: No Zapier-specific documentation or trigger registration. No retry on failure. No delivery log. No webhook test button. No payload template customization.  
Required Action:  
- Add webhook delivery log table in admin (URL, status code, response, timestamp)  
- Add retry mechanism (3 attempts with exponential backoff via WP Cron)  
- Add "Test Webhook" button that sends a sample payload  
- Add field mapping (select which fields to include in payload, rename keys)  
- Add Zapier-specific setup guide with trigger event name  
Free vs Pro Decision: Pro  
Reason: Webhook reliability is critical for business automations. Delivery logging builds trust in the integration.

---

**[P03.2] Mailchimp Integration**

Status: Pro — Correctly gated  
Current Behavior: Subscribe to list on form submission.  
Issue/Gap: No tag assignment. No field mapping UI (likely relies on field name matching). No double opt-in toggle. No audience segment selection.  
Required Action:  
- Add field mapping UI (map form fields to Mailchimp merge fields)  
- Add tag assignment (static tags + dynamic tags from field values)  
- Add double opt-in toggle  
- Add audience group/segment selection  
Free vs Pro Decision: Pro  
Reason: Email list building is a core business workflow. Mailchimp integration is the #1 requested integration across all form plugins.

---

**[P03.3] Missing Critical Integrations**

Status: Missing  
Current Behavior: Only Webhook, Google Sheets, Slack, Mailchimp in Pro.  
Issue/Gap: No Zapier native integration, no ActiveCampaign, no HubSpot, no ConvertKit/Kit, no Brevo/Sendinblue, no WooCommerce.  
Required Action (priority order):  
1. ConvertKit / Kit (large creator/blogger audience)  
2. ActiveCampaign (SMB CRM market leader)  
3. HubSpot (enterprise/SaaS market)  
4. Brevo / Sendinblue (EU privacy-conscious market)  
5. WooCommerce (native WP ecosystem sync)  
Free vs Pro Decision: Pro  
Reason: Integration breadth is the primary driver of Pro upgrade decisions for marketing teams. Each integration added expands the addressable audience.

---

**[P03.4] Google Sheets Integration**

Status: Pro — Needs Improvement  
Current Behavior: Auto-append rows to Google Sheets.  
Issue/Gap: No OAuth flow documentation. No sheet column mapping UI. No error handling when quota exceeded.  
Required Action:  
- Build OAuth2 connection flow with Google (connect/disconnect UI)  
- Add column mapping (map form fields to sheet columns)  
- Add delivery log and retry on API quota exceeded  
Free vs Pro Decision: Pro  
Reason: Google Sheets sync is the #2 most-requested integration after email marketing tools.

---

#### 🔹 CHAPTER P04: BUSINESS FEATURES

---

**[P04.1] Stripe Payments**

Status: Pro — Consider Strategic Split  
Current Behavior: Stripe PaymentIntent integration, one-time or recurring.  
Issue/Gap: WPForms and Fluent Forms both offer Stripe free with a transaction fee. GenForm locking Stripe fully behind Pro may be a missed activation opportunity.  
Required Action (Strategic Decision):  
- Option A (Current): Keep Stripe fully Pro — simpler licensing  
- Option B (Recommended): Offer Stripe free with 2% GenForm transaction fee; Pro removes the fee  
- Add coupon code support for Pro  
- Add Stripe webhooks for payment status updates (refunds, failures)  
Free vs Pro Decision: Strategic split recommended (free with fee → Pro removes fee)  
Reason: Payment capability drives form installs from eCommerce and event organizers. Transaction fee model generates revenue from free users while giving them immediate value.

---

**[P04.2] PDF Generation**

Status: Pro — Correctly gated  
Current Behavior: Generate PDFs from entries.  
Issue/Gap: PDF template system unclear — likely uses a simple HTML-to-PDF conversion. No branded PDF templates. No PDF attachment to confirmation email.  
Required Action:  
- Offer 3 PDF template layouts: Invoice, Certificate, Summary  
- Allow logo upload for branded PDFs  
- Add "Attach PDF to confirmation email" toggle  
- Add "Download PDF" button in entry detail view (free to view, Pro to generate)  
Free vs Pro Decision: Pro  
Reason: PDF generation is used for receipts, certificates, contracts — high business value Pro feature.

---

**[P04.3] User Registration**

Status: Pro — Correctly gated  
Current Behavior: Create WordPress user accounts from form submissions.  
Issue/Gap: No email verification flow. No duplicate user detection. No role-based field visibility post-registration.  
Required Action:  
- Add email verification (send verification link before account activation)  
- Detect existing email → prompt to login or show error  
- Support username auto-generation from first/last name  
Free vs Pro Decision: Pro  
Reason: User registration from forms is a SaaS onboarding pattern — clearly Pro territory.

---

**[P04.4] Landing Pages**

Status: Pro — Correctly gated  
Current Behavior: Distraction-free standalone form URLs.  
Issue/Gap: No custom domain support. No SEO meta fields. No custom header/footer. No embedded branding controls.  
Required Action:  
- Use WordPress template override (custom `page.php` with no theme header/footer)  
- Add landing page settings: title, description, cover image, background color, logo  
- Add Open Graph tags for social sharing  
Free vs Pro Decision: Pro  
Reason: Landing pages are used for advertising campaigns — agencies and marketers pay for this.

---

**[P04.5] Entry Import**

Status: Missing  
Current Behavior: Export only (CSV).  
Issue/Gap: No way to import entries from another form builder migration (Gravity Forms → GenForm). This is a blocker for agencies considering switching.  
Required Action:  
- Add CSV entry import with field mapping UI  
- Support importing from WPForms and Gravity Forms export format  
Free vs Pro Decision: Pro  
Reason: Entry import is a migration/agency tool — Pro-level workflow.

---

#### 🔹 CHAPTER P05: ANALYTICS & REPORTING

---

**[P05.1] Submission Analytics**

Status: Pro — Backend Exists, UI Needs Building ⚡  
Current Behavior: `Charts.php` exists in Pro with a working AJAX endpoint (`getChartData()`) that returns daily entries, form breakdown, and payment stats (Charts.php lines 29–104). Data is prepared — no frontend chart rendering confirmed.  
Completed: ✅ Pro analytics AJAX data endpoint (`genform_get_chart_data`) returning: daily entries per form, form breakdown, payment stats  
Issue/Gap: No admin page rendering the chart data. No frontend chart library (Chart.js) connected to the endpoint. No basic analytics in free plugin — Fluent Forms now includes this free (2025).  
Required Action:  
- Free: add analytics tab/page in free admin using data from `wp_genform_entries`; total + 30-day trend using Chart.js  
- Pro: connect `getChartData()` AJAX response to Chart.js charts on a dedicated Reports admin page; add conversion rate, heatmap, top forms  
Free vs Pro Decision: Split  
Reason: Backend data layer is ready in Pro. Frontend rendering needs to be built for both free (basic) and Pro (advanced) tiers.

---

**[P05.2] Conversion Tracking**

Status: Missing  
Current Behavior: No view-to-submission conversion tracking.  
Issue/Gap: Without conversion data, users cannot optimize their forms. This is a major UX and business intelligence gap.  
Required Action:  
- Log form views (a JavaScript call when form is rendered)  
- Calculate conversion rate = submissions / views  
- Display per-form conversion rate in analytics  
- Track conversion trend over time  
Free vs Pro Decision: Pro  
Reason: Conversion tracking is a marketing intelligence feature — Pro users need it to justify the plugin to stakeholders.

---

---

## PART 3: ARCHITECTURE & TECH

### 3.1 Free-Pro Relationship

Current model is correct: Pro is a separate plugin that hooks into Free via `plugins_loaded` at priority 20. Pro registers its feature list via `genform_pro_features` filter. Core Feature_Gate checks this filter.

**Recommendation:** Maintain this model. Do not merge Pro into Free. Do not use a "feature flag in single plugin" model. The separate plugin model is easier to maintain, license, and distribute.

**Hook contracts to formalize and document:**

| Hook | Type | Description |
|---|---|---|
| `genform_pre_submission` | Action | Before validation. Use for pre-processing, file upload initiation. |
| `genform_post_submission` | Action | After entry saved. Args: `$entry_id, $form_id, $entry_data`. Use for integrations. |
| `genform_admin_scripts` | Action | After admin JS/CSS enqueued. Use for Pro builder assets. |
| `genform_builder_field_settings` | Action | Inside field settings panel. Use for Pro field settings (conditional logic). |
| `genform_builder_tabs` | Filter | Add/modify builder tabs. Args: `$tabs`. |
| `genform_pro_features` | Filter | Register Pro features. Args: `$features` (array). |
| `genform_is_pro_active` | Filter | Check Pro status. Args: `$is_active` (bool). |
| `genform_field_attributes` | Filter | Modify field HTML attributes. Use for conditional logic data attributes. |
| `genform_form_output` | Filter | Modify complete form HTML before output. |

### 3.2 Database Schema — Current & v2 Roadmap

**Current schema is production-ready for < 100K entries per form.** No changes needed for v1.x.

**v2 schema consideration (when entries reach scale):**

The `entry_data` JSON column prevents SQL-level field queries. For future search, filtering, and reporting features, consider:
- `wp_genform_entry_meta` table: `(id, entry_id, field_key, field_value)` — enables indexed queries on specific field values
- Migration: write new entries to both `entry_data` (JSON) and `entry_meta` (normalized); read from `entry_meta` for search/filter

This is a v2.0 item, not a v1.x priority.

### 3.3 REST API Roadmap

Traditional `wp_ajax_*` is sufficient for v1.x. For v2.0, add REST API:

```
GET    /wp-json/genform/v1/forms              # List forms
GET    /wp-json/genform/v1/forms/{id}         # Get form schema
POST   /wp-json/genform/v1/forms/{id}/submit  # Submit form (headless)
GET    /wp-json/genform/v1/entries            # List entries (admin)
GET    /wp-json/genform/v1/entries/{id}       # Get entry detail
DELETE /wp-json/genform/v1/entries/{id}       # Delete entry
```

Authentication: use WordPress nonce for admin endpoints, application passwords for external API clients.

### 3.4 Performance Architecture

| Concern | Current | Recommendation |
|---|---|---|
| Email sending | Synchronous (blocks submission) | Queue via `wp_schedule_single_event()` |
| Entry counts | Real-time DB query + 5-min transient | Object cache + invalidate on insert/delete |
| CSV export | Streaming 500-row chunks | Maintain — this is correct |
| Webhook delivery | Synchronous | Queue via WP Cron with retry |
| Form rendering | DB query per page load | Add Redis/persistent object cache support |
| Detection_Helper | Every submission | Remove or make optional |

### 3.5 Security Architecture

**Current security posture is strong.** Maintain:
- Per-form nonce tokens (not global)
- Honeypot + CAPTCHA combination
- Rate limiting (fix to per-form per F02.4)
- Prepared SQL throughout
- Capability checks on all admin AJAX handlers

**Add:**
- Content Security Policy (CSP) nonce for inline scripts
- File upload virus scanning hook (Pro)
- Webhook signature verification (HMAC secret on outbound webhooks — Pro)
- Entry data encryption option (Pro — GDPR enterprise use case)

---

## PART 4: PRIORITY TAGGING

| Feature ID | Title | Priority | Impact Area | Effort | Status |
|---|---|---|---|---|---|
| F01.7 | Undo/Redo in Builder | P0 | UX / Retention | Medium | ❌ Missing |
| F02.1 | Client-Side Validation | P0 | UX / Activation | Medium | ⚡ Partial (checkbox only) |
| F05.4 | Onboarding Wizard | P0 | Activation / Retention | High | ❌ Missing |
| F05.1 | Last Submission + Embed Wizard | P0 | UX / Dashboard | Low | ❌ Missing |
| F03.4 | Async Email Sending | P0 | Reliability | Low | ❌ Missing |
| F02.4 | Rate Limiting — Per-Form Fix | P0 | Correctness | Low | ❌ Missing |
| F05.1 | Forms List — Stats + Shortcode Copy | — | UX / Dashboard | — | ✅ Done |
| F03.3 | Confirmation Email Field Selector | — | Correctness | — | ✅ Done |
| F01.1 | Builder Field Duplication | — | UX | — | ✅ Done |
| F01.2 | Tel/Phone Field in Free | — | Competitive Parity | — | ✅ Done |
| F06.2 | Form Loading State + Animations | — | UX Polish | — | ✅ Done |
| F06.1 | Gutenberg Block Enhancement | P1 | Discovery / UX | Medium | ⚡ Partial (no ServerSideRender) |
| F02.2 | reCAPTCHA v3 + hCaptcha + Turnstile | P1 | Spam / Trust | Medium | ❌ Missing |
| F02.3 | Akismet Integration | P1 | Spam / Trust | Low | ❌ Missing |
| F01.6 | URL Parameter Prefill | P1 | Marketing / Pro trigger | Low | ❌ Missing |
| F05.2 | Basic Submission Analytics | P1 | Retention | Medium | ❌ Missing |
| F05.3 | Settings Page Restructure | P1 | UX | Low | ❌ Missing |
| F01.2 | Section Break Field to Free | P1 | Competitive Parity | Low | ❌ Missing |
| F04.1 | Entry Date Range Filter | P1 | Data Management | Low | ❌ Missing (search ✅ done) |
| F04.3 | Prev/Next Entry Navigation | P1 | UX | Low | ❌ Missing |
| F06.2 | Dark Mode CSS | P1 | UX Polish | Low | ❌ Missing |
| F03.1 | CC/BCC on Notifications | P2 | Collaboration | Low | ❌ Missing |
| F06.4 | Template Search + Preview | P2 | Activation | Medium | ❌ Missing |
| F01.3 | Builder Tab Restructure | P2 | UX | Medium | ⚡ Partial (3 of 5 tabs exist) |
| P01.1 | File Upload Enhancements | P2 | Pro Polish | Medium | ❌ Missing |
| P02.1 | Conditional Logic — Form-Level Extend | P2 | Pro Polish | High | ⚡ Partial (field-level UI done) |
| P02.2 | Multi-Step — Step Validation | P2 | Pro Polish | Medium | ❌ Missing |
| P03.1 | Webhook Delivery Logs + Retry | P2 | Pro Reliability | Medium | ❌ Missing |
| P03.2 | Mailchimp Field Mapping UI | P2 | Pro Polish | Medium | ⚡ Partial (auto-detect only) |
| P03.3 | ConvertKit Integration | P2 | Pro Growth | Medium | ❌ Missing |
| P04.1 | Stripe Free Tier (with fee) | P2 | Activation | High | ❌ Strategic decision needed |
| P05.1 | Pro Analytics Dashboard UI | P2 | Pro Value | High | ⚡ Partial (data endpoint done) |
| P05.2 | Conversion Rate Tracking | P2 | Pro Value | High | ❌ Missing |
| P02.3 | Calculations Formula Builder UI | P3 | Pro Polish | High | ❌ Missing |
| P03.3 | ActiveCampaign Integration | P3 | Pro Growth | Medium | ❌ Missing |
| P03.3 | HubSpot Integration | P3 | Pro Growth | Medium | ❌ Missing |
| P04.2 | PDF Templates + Email Attachment | P3 | Pro Value | High | ❌ Missing |
| P04.5 | Entry Import (CSV) | P3 | Agency/Migration | High | ❌ Missing |
| P02.4 | Save & Resume (Expiry + Admin View) | P3 | Pro Polish | Medium | ❌ Missing |
| F06.3 | Per-Form Color Override | P3 | UX | Low | ❌ Missing |
| F02.5 | Entry Count Limits | P3 | Pro | Low | ❌ Missing |
| F02.6 | Form Scheduling | P3 | Pro | Low | ❌ Missing |

---

## PART 5: FINAL PRODUCT STRATEGY SUMMARY

### 5.1 What Goes FREE

**The principle:** Free GenForm must be the best free contact form plugin for users who want real data control, honest UX, and zero artificial limits on basic functionality.

| Category | Free Includes |
|---|---|
| Forms | Unlimited forms, unlimited fields, unlimited entries |
| Fields | Text, Email, Textarea, Number, Select, Radio, Checkbox, Date, URL, Tel, Phone (basic), Hidden, Password, Section Break |
| Builder | Drag-drop, field duplication, undo/redo, field search |
| Submissions | AJAX, honeypot, reCAPTCHA v2/v3, hCaptcha, Turnstile, Akismet, rate limiting (per-form) |
| Notifications | 1 admin notification (CC/BCC/Reply-To), 1 confirmation email |
| Entries | WP DB storage, date filter, search, CSV export, star/read/trash |
| Analytics | Basic: total submissions + 30-day trend chart per form |
| Templates | All 7 template categories (Pro-field templates labeled) |
| Block/Shortcode | Gutenberg block (with preview), shortcode, embed wizard |
| Portability | JSON import/export, URL parameter prefill |
| Onboarding | Activation wizard, empty states, shortcode copy button |
| Settings | Organized tabs: General, Email, Spam, Appearance |

### 5.2 What Is PRO Only

| Category | Pro Only |
|---|---|
| Fields | File Upload, Signature, Star Rating, Repeater, Address (autocomplete), Rich Text, Survey/NPS, Phone (country code) |
| Logic | Conditional logic, Multi-step forms, Calculations, Save & Resume, Form Abandonment |
| Notifications | Multiple notifications, conditional routing, email with file attachments |
| Confirmations | Conditional confirmation messages |
| Entry Management | Entry editing, column selector, Excel/JSON export, entry import, bulk PDF export |
| Scheduling | Form open/close by date, entry count limits |
| Payments | Stripe (fee-free), PayPal, Coupons |
| Integrations | Mailchimp (+mapping), ConvertKit, ActiveCampaign, HubSpot, Brevo, Zapier/Make, Google Sheets (+mapping), Slack |
| Analytics | Conversion rates, field drop-off, heatmaps, comparison views |
| Business | User Registration, Post Creation, PDF Generation, Landing Pages, Geolocation, Entry Import |
| Developer | REST API, white-label, multisite management |

### 5.3 What Is SPLIT (Free-Tease → Pro-Convert)

| Feature | Free Version | Pro Version |
|---|---|---|
| Email notification | 1 (CC/BCC included) | Unlimited + conditional |
| Spam protection | Honeypot + reCAPTCHA + hCaptcha + Turnstile + Akismet | + Keyword blocking + country/IP restriction |
| Entry management | View + CSV export | + Edit + Excel/JSON + import |
| Analytics | Basic count + trend | Full charts + conversion + heatmap |
| Templates | All categories, Pro-field labeled | Exclusive premium design templates |
| Phone field | Basic phone (text + validation) | Country code selector |
| Stripe | Optional: free with 2% fee | No transaction fee |
| Form styling | Global color + per-form override | Advanced CSS, typography, animations |

### 5.4 Activation Strategy

**Goal:** First submission received within 10 minutes of plugin activation.

1. **Welcome screen** on first activation — not a settings page, a guided journey
2. **Template first** — lead with templates, not a blank canvas
3. **Smart defaults** — pre-fill notification email with `admin_email`, pre-fill "Thank you" message
4. **Embed guidance** — after save, show shortcode + "Add block" button, not just a settings page
5. **First submission toast** — when first entry arrives, show a congratulatory in-admin notification
6. **7-day drip** (via Freemius opt-in) — surface one underused feature per email:
   - Day 1: "Your form is live — here's how to see submissions"
   - Day 3: "Add a confirmation email for your visitors"
   - Day 5: "Export your entries as CSV"
   - Day 7: "Upgrade to add file uploads, conditional logic, and more"

### 5.5 Upgrade Triggers (Contextual, Non-Aggressive)

| Trigger | Location | CTA |
|---|---|---|
| User adds 5+ fields | Builder | "Add conditional logic to show/hide fields based on answers →" |
| User sets up email notification | Email tab | "Need to notify multiple team members? Upgrade to Pro →" |
| User reaches 100 entries | Entries list header | "Unlock entry editing, bulk PDF export, and advanced filtering →" |
| User clicks file upload field | Builder field panel | "Accept file uploads from your visitors — Pro feature →" |
| User searches for "Mailchimp" in settings | Settings page | "Connect your form to Mailchimp with one click — Pro →" |
| User views entry detail | Entry detail page | "Edit this entry data — available in Pro →" |
| User exports CSV | Export area | "Need Excel or JSON format? Available in Pro →" |

**Upgrade CTA design principle:** Show upgrade prompts only at the moment of need. Never show them on the dashboard overview or forms list. Never use popup modals for upgrade prompts — use inline cards with a dismiss option.

### 5.6 Monetization Logic

**Pricing recommendation (based on competitive analysis):**
- Personal: $49/year (1 site) — all Pro features
- Agency: $99/year (5 sites) — all Pro features + priority support
- Unlimited: $199/year (unlimited sites) — white-label + agency management

**Revenue model:**
- Primary: Annual license subscriptions (Freemius)
- Secondary: Transaction fee on free Stripe tier (if implemented — 2% with $0.50 cap per transaction)
- Tertiary: Premium template packs ($19/pack — healthcare forms, legal forms, government forms)

**Free → Pro conversion benchmarks to target:**
- Year 1: 2% of active installs (industry average: 1-3%)
- Year 2: 3.5% of active installs (with improved onboarding and upgrade triggers)
- Target ARPU: $79/year blended across tiers

---

## APPENDIX: RELEASE SEQUENCING

### v1.4.0 — UX Foundation (4-6 weeks)
- [ ] Client-side validation — inline blur/submit (F02.1) ⚡ PARTIAL: checkbox/GDPR only; text, email, number validation missing
- [ ] Per-form rate limit fix (F02.4) — change transient key to include form_id
- [x] ✅ Confirmation email field selector (F03.3) — `gfm_conf_to_field` implemented in Email.php
- [ ] Async email sending (F03.4) — queue via `wp_schedule_single_event()`
- [x] ✅ Shortcode copy button on forms list (F05.1) — `gfm-shortcode-copy` div implemented
- [x] ✅ Submission count on forms list (F05.1) — `e_c` column with clickable badge
- [x] ✅ Form loading state + success animation (F06.2) — spinner + `gfm-fade-in` keyframe
- [x] ✅ Field duplication in builder (F01.1) — `duplicateField()` in form-builder.js
- [x] ✅ Tel/Phone field in free tier (F01.2) — `type="tel"` already in form-template.php, not gated
- [x] ✅ Empty state designs — SVG + CTA in forms-list.php
- [ ] Add Section Break/Divider field to free (F01.2 — remaining gap)
- [ ] "Last Submission" date column on forms list (F05.1 — remaining gap)

### v1.5.0 — Spam & Trust (3-4 weeks)
- [ ] reCAPTCHA v3 support (F02.2)
- [ ] hCaptcha support (F02.2)
- [ ] Cloudflare Turnstile support (F02.2)
- [ ] Akismet integration (F02.3)

### v1.6.0 — Discovery & Activation (4-6 weeks)
- [ ] Onboarding wizard (F05.4)
- [ ] URL parameter prefill (F01.6)
- [ ] Embed wizard/modal after form save (F05.1 remaining)
- [ ] Template search + preview (F06.4)
- [ ] Basic submission analytics chart in free (F05.2)
- [ ] Settings page restructure into tabs (F05.3)
- [ ] Gutenberg block — ServerSideRender preview + "Create new form" button (F06.1)

### v1.7.0 — Builder Polish (3-4 weeks)
- [ ] Undo/redo in builder (F01.7)
- [ ] Builder tab restructure: Fields | Notifications | Confirmation | Design | Security (F01.3)
- [ ] Per-form color override in Design tab (F06.3)
- [ ] Entry date range filter (F04.1 remaining — search already done)
- [ ] Prev/Next navigation in entry detail (F04.3)
- [ ] CC/BCC in admin notification (F03.1)
- [ ] Dark mode CSS for frontend (F06.2 remaining)

### v2.0.0 — Pro Maturity (8-12 weeks)
- [ ] Conditional logic — extend to form-level redirect + confirmation messages (P02.1) ⚡ PARTIAL: field-level UI exists
- [ ] Multi-step — enforce field validation before step advance (P02.2)
- [ ] Webhook delivery log table + retry via WP Cron (P03.1)
- [ ] Mailchimp field mapping UI (P03.2) ⚡ PARTIAL: auto-detection exists, visual mapping UI missing
- [ ] ConvertKit integration (P03.3)
- [ ] Pro analytics dashboard UI — connect Charts.php AJAX to Chart.js frontend (P05.1) ⚡ PARTIAL: data endpoint exists
- [ ] Conversion rate tracking — form view logging + rate calculation (P05.2)
- [ ] PDF templates + email attachment option (P04.2)
- [ ] Stripe free tier with 2% transaction fee (P04.1 — strategic decision required)

---

*Document maintained by: GenForm Product Team*  
*Next review: 2026-08-01*
