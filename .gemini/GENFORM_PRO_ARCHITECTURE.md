# GenForm Pro — Architecture & Strategy Blueprint

> **Audience:** Developer / Decision-Maker  
> **Date:** February 18, 2026  
> **Status:** Planning Phase  

---

## 1. Executive Summary

GenForm Free (v1.1.0) is a fully functional, professional-grade form builder. It already
competes feature-for-feature with WPForms Lite, Contact Form 7, and Forminator Free.
The Pro edition will target power users, agencies, and businesses by offering the
four most-requested premium capabilities in the WordPress ecosystem:

1. **Conditional Logic** — dynamic show/hide fields
2. **Multi-Step Forms** — break long forms into pages
3. **File Upload** — accept documents and media  
4. **Payment Integration** — collect payments via Stripe/PayPal

These four features alone drive 80%+ of form builder upgrades industry-wide.

---

## 2. Free vs Pro Feature Matrix

### What Stays Free (Current v1.1.0)

| Category           | Feature                                              |
|--------------------|------------------------------------------------------|
| **Builder**        | 10 field types, drag-and-drop, field reorder         |
| **Builder**        | Per-field: label, placeholder, required, CSS, width  |
| **Builder**        | Default values, multi-option management              |
| **Templates**      | 21+ pre-made templates across 7 categories           |
| **Entries**        | WP_List_Table with filter, search, bulk actions      |
| **Entries**        | Quick-view modal, entry detail page, read/unread     |
| **Entries**        | CSV export (per-form & all combined)                 |
| **Email**          | Admin notification with template tags                |
| **Email**          | Configurable subject, body, from, reply-to           |
| **Settings**       | Brand color, reCAPTCHA keys, email identity          |
| **Embed**          | Gutenberg Block + Shortcode                          |
| **Security**       | Honeypot, rate limiting, nonce, GDPR consent         |
| **Admin**          | Dashboard widget, admin bar links, form duplication  |
| **Dev**            | PSR-4, PHP 8.3, SCSS, full i18n                      |

### What Goes Pro (Planned)

| Priority | Feature                       | Revenue Impact | Build Effort |
|----------|-------------------------------|----------------|--------------|
| 🔴 P0   | Conditional Logic             | ★★★★★          | High         |
| 🔴 P0   | Multi-Step / Page Break       | ★★★★☆          | Medium       |
| 🟡 P1   | File Upload Field             | ★★★★☆          | Medium       |
| 🟡 P1   | Stripe Payment Integration    | ★★★★★          | High         |
| 🟡 P1   | Visual Data Reports           | ★★★☆☆          | Medium       |
| 🟢 P2   | Conversational Forms          | ★★★☆☆          | High         |
| 🟢 P2   | Quiz / Survey Mode            | ★★★☆☆          | Medium       |
| 🟢 P2   | User Registration Fields      | ★★☆☆☆          | Medium       |
| 🟢 P2   | Calculated Fields             | ★★★★☆          | High         |
| 🔵 P3   | PayPal Integration            | ★★★☆☆          | Medium       |
| 🔵 P3   | Mailchimp / CRM Integration   | ★★★☆☆          | Medium       |
| 🔵 P3   | Zapier / Webhook              | ★★★☆☆          | Low          |
| 🔵 P3   | PDF Generation (Entry → PDF)  | ★★☆☆☆          | Medium       |
| 🔵 P3   | Advanced Entry Filters / Search| ★★☆☆☆          | Low          |
| 🔵 P3   | Form Scheduling / Expiry      | ★★☆☆☆          | Low          |
| 🔵 P3   | Entry Editing (Frontend)      | ★★☆☆☆          | Medium       |
| 🔵 P3   | Role-Based Access Control     | ★★☆☆☆          | Low          |

---

## 3. Technical Architecture

### 3.1 Plugin Structure: Free + Pro Add-On

The Pro functionality ships as a **separate companion plugin** (`genform-pro`),
not a replacement for the free plugin. This follows the proven model used by
WPForms, Fluent Forms, and Formidable:

```
wp-content/plugins/
├── genform/                  ← Free (WordPress.org)
│   ├── includes/
│   │   ├── Core.php
│   │   ├── Pro/
│   │   │   └── FeatureGate.php      ← Lightweight gate (always present)
│   │   ├── Admin/
│   │   ├── Handlers/
│   │   ├── Integrations/
│   │   └── Templates/
│   └── ...
│
└── genform-pro/              ← Pro (distributed separately)
    ├── genform-pro.php       ← Bootstrap, checks genform is active
    ├── includes/
    │   ├── License.php       ← Activation / validation
    │   ├── ProCore.php       ← Registers pro features via hooks
    │   ├── Fields/
    │   │   ├── FileUpload.php
    │   │   └── ...
    │   ├── Logic/
    │   │   ├── ConditionalEngine.php
    │   │   └── PageBreak.php
    │   ├── Payments/
    │   │   ├── StripeHandler.php
    │   │   └── PayPalHandler.php
    │   ├── Reporting/
    │   │   └── Charts.php
    │   └── Integrations/
    │       ├── Mailchimp.php
    │       └── Webhook.php
    └── assets/
        ├── js/
        │   └── pro-builder.js
        └── scss/
            └── pro-admin.scss
```

### 3.2 Feature Gate System (Free Plugin Side)

A lightweight `FeatureGate` class lives in the free plugin. It does two things:

1. Checks if genform-pro is active and licensed
2. Renders "Pro" badge UI on locked features (upsell hooks)

```php
<?php
namespace GenForm\Pro;

final class FeatureGate
{
    /**
     * Check whether a specific Pro feature is available.
     *
     * @param string $feature Feature slug (e.g. 'conditional_logic').
     */
    public static function has(string $feature): bool
    {
        // Pro plugin registers its features via this filter.
        $features = apply_filters('genform_pro_features', []);
        return in_array($feature, $features, true);
    }

    /**
     * Check if any Pro license is active.
     */
    public static function isProActive(): bool
    {
        return apply_filters('genform_is_pro_active', false);
    }

    /**
     * Render a "PRO" badge for the builder UI.
     */
    public static function proBadge(string $feature, string $label = ''): string
    {
        if (self::has($feature)) {
            return ''; // Feature unlocked, no badge needed.
        }
        $text = $label ?: esc_html__('PRO', 'genform');
        return sprintf(
            '<span class="gfm-pro-badge" data-feature="%s" title="%s">%s</span>',
            esc_attr($feature),
            esc_attr__('Upgrade to GenForm Pro', 'genform'),
            esc_html($text)
        );
    }
}
```

### 3.3 Pro Plugin Bootstrap

```php
<?php
/**
 * Plugin Name: GenForm Pro
 * Description: Advanced features for GenForm — Conditional Logic, Multi-Step, Payments & more.
 * Requires Plugins: genform
 * Version: 1.0.0
 */

namespace GenFormPro;

if (! defined('ABSPATH')) {
    exit;
}

// Verify free plugin is active.
add_action('plugins_loaded', function () {
    if (! defined('GENFORM_VERSION')) {
        add_action('admin_notices', function () {
            printf(
                '<div class="notice notice-error"><p>%s</p></div>',
                esc_html__('GenForm Pro requires the GenForm plugin to be installed and active.', 'genform-pro')
            );
        });
        return;
    }

    // Verify license.
    if (! License::isValid()) {
        return;
    }

    // Register Pro feature list.
    add_filter('genform_pro_features', function (array $features): array {
        return array_merge($features, [
            'conditional_logic',
            'multi_step',
            'file_upload',
            'payment_stripe',
            'visual_reports',
        ]);
    });

    add_filter('genform_is_pro_active', '__return_true');

    // Boot Pro modules.
    ProCore::instance()->init();
});
```

### 3.4 Hook-Based Extension Points (Free Plugin)

The free plugin needs minimal changes to support Pro features. We add
filter/action hooks at strategic points — the Pro plugin hooks in:

| Hook Name                          | Location          | Purpose                                   |
|------------------------------------|-------------------|-------------------------------------------|
| `genform_builder_field_types`      | form-builder.php  | Register additional field types            |
| `genform_builder_field_settings`   | form-builder.js   | Inject per-field settings panels           |
| `genform_before_field_render`      | Shortcode.php     | Inject conditional logic attributes        |
| `genform_after_form_render`        | Shortcode.php     | Add page break navigation, payment UI     |
| `genform_pre_submission`           | FormHandler.php   | Validate file uploads, process payments    |
| `genform_post_submission`          | FormHandler.php   | Trigger integrations (Mailchimp, webhook)  |
| `genform_entry_detail_sidebar`     | entry-detail.php  | Add payment receipt, file download links   |
| `genform_admin_scripts`            | Core.php          | Enqueue pro admin JS/CSS                   |
| `genform_settings_sections`        | Settings.php      | Add Pro settings (Stripe keys, etc.)       |
| `genform_form_settings_tabs`      | form-builder.php  | Add new tabs (Logic, Payments)             |
| `genform_pro_features`             | FeatureGate.php   | Register available Pro features            |
| `genform_is_pro_active`            | FeatureGate.php   | Boolean license check                      |

---

## 4. Upsell Strategy — In-Product Conversion Points

The free plugin should make Pro features **visible but locked**. Users see what
they're missing at the exact moment they need it. Here's where to place upsell
touchpoints:

### 4.1 Builder Sidebar — Locked Field Types

```
┌──────────────────────────┐
│  Available Fields        │
│  ┌──────┐ ┌──────┐      │
│  │ Text │ │ Email│      │   ← Free fields
│  └──────┘ └──────┘      │
│  ... 8 more ...          │
│                          │
│  ── Pro Fields ──────    │
│  ┌──────────────┐ [PRO]  │
│  │ File Upload  │        │   ← Visible but locked
│  └──────────────┘        │
│  ┌──────────────┐ [PRO]  │
│  │ Page Break   │        │
│  └──────────────┘        │
│  ┌──────────────┐ [PRO]  │
│  │ Signature    │        │
│  └──────────────┘        │
│  ┌──────────────┐ [PRO]  │
│  │ Star Rating  │        │
│  └──────────────┘        │
│  ┌──────────────┐ [PRO]  │
│  │ Payment      │        │
│  └──────────────┘        │
└──────────────────────────┘
```

Clicking a locked field opens a **tasteful upgrade modal** (not aggressive popup).

### 4.2 Builder Tab — Logic Tab (Locked)

Add a 4th tab to the builder header:

```
[ Fields ] [ Settings ] [ Notifications ] [ Logic 🔒 PRO ]
```

When clicked, show a polished landing section explaining conditional logic
with an animated SVG example and an "Unlock" CTA.

### 4.3 Entries Page — Visual Reports (Locked)

Add a "Reports" tab or icon next to the export button:

```
[ Export CSV ]  [ 📊 Visual Reports — PRO ]
```

Clicking shows a blurred/sample chart with an upgrade overlay.

### 4.4 Settings Page — Pro Section

```
┌──────────────────────────────────────────┐
│  🔓 Pro Integrations                     │
│                                          │
│  Connect Stripe, PayPal, Mailchimp,      │
│  Zapier and more to supercharge your     │
│  forms.                                  │
│                                          │
│  [ Upgrade to GenForm Pro → ]            │
└──────────────────────────────────────────┘
```

### 4.5 Template Library — Pro Templates

Mark 30% of templates as "Pro Only" with a subtle lock icon. This shows
users the breadth of what's available while keeping the majority free.

---

## 5. Licensing System

### 5.1 License Key Validation

Use a standard WordPress license server (EDD Software Licensing or
WooCommerce Software Add-on). The flow:

```
User enters key → AJAX POST → License server validates →
  ├── Valid:   Store in wp_options, activate pro hooks
  └── Invalid: Show inline error, features stay gated
```

**Storage:**

```php
// Option: genform_pro_license
[
    'key'        => 'GP-XXXX-XXXX-XXXX',
    'status'     => 'valid',           // valid | expired | disabled
    'expires'    => '2027-02-18',
    'activated'  => '2026-02-18',
    'site_count' => 1,
    'plan'       => 'agency',          // personal | agency | unlimited
]
```

**Tiers (recommended):**

| Plan       | Sites | Price/yr | Features                          |
|------------|-------|----------|-----------------------------------|
| Personal   | 1     | $49      | All Pro features, 1 site          |
| Agency     | 5     | $99      | All Pro features, 5 sites         |
| Unlimited  | ∞     | $149     | All Pro features, unlimited sites |
| Lifetime   | ∞     | $349     | One-time, lifetime updates        |

### 5.2 Grace Period & Degradation

- Expired license → Pro features stay active for **14-day grace period**
- After grace → Features gracefully degrade (not crash):
  - Conditional logic stops evaluating but form still renders all fields
  - File uploads disabled with a notice
  - Payment fields show a maintenance message
  - Existing entries/files remain accessible

---

## 6. Database Schema Extensions

Pro features need additional tables/columns:

```sql
-- Pro: File uploads meta (links to entries)
CREATE TABLE {prefix}genform_files (
    id           BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    entry_id     BIGINT UNSIGNED NOT NULL,
    form_id      BIGINT UNSIGNED NOT NULL,
    field_name   VARCHAR(100) NOT NULL,
    file_name    VARCHAR(255) NOT NULL,
    file_path    TEXT NOT NULL,
    file_type    VARCHAR(50) NOT NULL,
    file_size    BIGINT UNSIGNED DEFAULT 0,
    uploaded_at  DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_entry (entry_id),
    INDEX idx_form  (form_id)
);

-- Pro: Payment records
CREATE TABLE {prefix}genform_payments (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    entry_id        BIGINT UNSIGNED NOT NULL,
    form_id         BIGINT UNSIGNED NOT NULL,
    gateway         VARCHAR(20) NOT NULL,         -- stripe | paypal
    transaction_id  VARCHAR(255) NOT NULL,
    amount          DECIMAL(10,2) NOT NULL,
    currency        VARCHAR(3) DEFAULT 'USD',
    status          VARCHAR(20) DEFAULT 'pending', -- pending | completed | refunded | failed
    payer_email     VARCHAR(255) DEFAULT '',
    meta            LONGTEXT,                     -- JSON: receipt_url, etc.
    created_at      DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_entry   (entry_id),
    INDEX idx_gateway (gateway),
    INDEX idx_status  (status)
);
```

---

## 7. Conditional Logic — Deep Design

This is the #1 most-requested Pro feature across all form builders.

### 7.1 Data Model

Conditional rules are stored per-field inside the existing `form_data` JSON:

```json
{
  "fields": [
    {
      "id": "field_1",
      "type": "select",
      "label": "Are you a student?",
      "options": [
        { "label": "Yes", "value": "yes" },
        { "label": "No", "value": "no" }
      ]
    },
    {
      "id": "field_2",
      "type": "text",
      "label": "University Name",
      "conditional": {
        "enabled": true,
        "action": "show",
        "logic": "all",
        "rules": [
          {
            "field": "field_1",
            "operator": "is",
            "value": "yes"
          }
        ]
      }
    }
  ]
}
```

### 7.2 Operators

| Operator       | Applies To            |
|----------------|-----------------------|
| `is`           | All field types       |
| `is_not`       | All field types       |
| `contains`     | Text, Textarea, Email |
| `not_contains` | Text, Textarea, Email |
| `starts_with`  | Text, Email, URL      |
| `ends_with`    | Text, Email, URL      |
| `greater_than` | Number                |
| `less_than`    | Number                |
| `is_empty`     | All                   |
| `is_not_empty` | All                   |

### 7.3 Frontend Engine (JS)

```javascript
// Lightweight conditional evaluator
const evaluateConditions = (fields, formData) => {
    fields.forEach(field => {
        if (!field.conditional?.enabled) return;

        const { action, logic, rules } = field.conditional;
        const results = rules.map(rule => {
            const value = formData[rule.field] ?? '';
            return evaluateRule(rule.operator, value, rule.value);
        });

        const passed = logic === 'all'
            ? results.every(Boolean)
            : results.some(Boolean);

        const shouldShow = action === 'show' ? passed : !passed;
        toggleField(field.id, shouldShow);
    });
};
```

---

## 8. Multi-Step Forms — Design

### 8.1 Concept

A special "Page Break" field type splits the form canvas into steps.
The builder previews this as divider lines with step labels.

### 8.2 Frontend Rendering

```html
<form class="genform gfm-multistep" data-steps="3">
  <div class="gfm-step-progress">
    <div class="gfm-step active" data-step="1">Personal Info</div>
    <div class="gfm-step" data-step="2">Address</div>
    <div class="gfm-step" data-step="3">Confirm</div>
  </div>

  <div class="gfm-step-body active" data-step="1">
    <!-- Fields for step 1 -->
  </div>
  <div class="gfm-step-body" data-step="2">
    <!-- Fields for step 2 -->
  </div>
  <div class="gfm-step-body" data-step="3">
    <!-- Fields for step 3, submit button -->
  </div>

  <div class="gfm-step-nav">
    <button type="button" class="gfm-step-prev">← Previous</button>
    <button type="button" class="gfm-step-next">Next →</button>
  </div>
</form>
```

---

## 9. File Upload — Design

### 9.1 Security Model

- Files stored in `wp-content/uploads/genform/{form_id}/{entry_id}/`
- `.htaccess` + `index.php` protection
- MIME type validation (server-side, not just extension)
- Max file size configurable per field (default: 5MB)
- Allowed types configurable (e.g., `jpg,png,pdf,doc,docx`)
- Files linked to entry via `genform_files` table

### 9.2 Builder UI

```
┌──────────────────────────────┐
│ File Upload Field Settings   │
│                              │
│ Label: [Upload Resume      ] │
│ Max Size: [5] MB             │
│ Allowed: [pdf,doc,docx     ] │
│ Max Files: [3]               │
│ Required: [✓]                │
└──────────────────────────────┘
```

---

## 10. Payment Integration — Design

### 10.1 Stripe Checkout Flow

```
User fills form → submits →
  GenForm JS creates Stripe PaymentIntent →
    Stripe iframe collects card →
      On success: submission proceeds with transaction ID →
        Entry stored with payment record
```

### 10.2 Builder Payment Settings

A new builder tab: **[ Payments ]**

```
┌──────────────────────────────┐
│ Enable Payments [✓]         │
│                              │
│ Payment Type:                │
│ ○ Fixed Amount               │
│ ○ User Defined               │
│ ○ From Field Value           │
│                              │
│ Amount: [$29.99]             │
│ Currency: [USD ▾]            │
│ Description: [Service Fee]   │
│                              │
│ Gateway: [Stripe ▾]          │
│ Live Mode: [✓]               │
└──────────────────────────────┘
```

---

## 11. Implementation Roadmap

### Phase 1: Foundation (Weeks 1-2)

- [ ] Create `genform-pro` plugin skeleton
- [ ] Implement `FeatureGate` in free plugin
- [ ] Add extension hooks to free plugin (filter/action points)
- [ ] Add "Pro" badges to builder sidebar (locked field types)
- [ ] Add locked "Logic" tab to builder
- [ ] Design upgrade modal/page
- [ ] Implement license activation system
- [ ] Create `genform_files` and `genform_payments` tables

### Phase 2: Core Pro Features (Weeks 3-6)

- [ ] Conditional Logic engine (PHP + JS)
- [ ] Conditional Logic builder UI (rules editor panel)
- [ ] Multi-Step / Page Break field type
- [ ] Multi-Step progress bar & navigation
- [ ] File Upload field (builder + frontend + storage)
- [ ] File management in entry detail

### Phase 3: Payments (Weeks 7-8)

- [ ] Stripe integration (PaymentIntent flow)
- [ ] Payment builder tab
- [ ] Payment entry records
- [ ] Stripe webhook handler (payment confirmation)

### Phase 4: Intelligence & Integrations (Weeks 9-12)

- [ ] Visual Reports (Chart.js based)
- [ ] Mailchimp integration
- [ ] Webhook / Zapier integration
- [ ] Calculated fields
- [ ] Pro template pack (30+ additional templates)

### Phase 5: Polish & Launch (Weeks 13-14)

- [ ] End-to-end testing
- [ ] Documentation site
- [ ] Pricing page
- [ ] Marketing assets
- [ ] WordPress.org readme update (free version)
- [ ] Launch

---

## 12. Free Plugin Changes Required

The free plugin needs these **minimal, non-breaking** additions to support Pro:

| File                  | Change                                                         |
|-----------------------|----------------------------------------------------------------|
| `Core.php`            | Add `genform_admin_scripts` action hook                        |
| `Core.php`            | Register `FeatureGate` autoload                                |
| `form-builder.php`    | Add `genform_builder_field_types` filter for sidebar           |
| `form-builder.php`    | Add `genform_form_settings_tabs` filter for tabs               |
| `form-builder.php`    | Render Pro field badges from FeatureGate                       |
| `FormHandler.php`     | Add `genform_pre_submission` / `genform_post_submission` hooks |
| `Shortcode.php`       | Add `genform_before_field_render` / `genform_after_form_render`|
| `Settings.php`        | Add `genform_settings_sections` filter                         |
| `entry-detail.php`    | Add `genform_entry_detail_sidebar` action hook                 |
| `_templates.scss`     | Add `.gfm-pro-badge` styles                                   |
| `admin.js`            | Add pro-upgrade modal handler                                  |

**Estimated impact:** ~150 lines added to free plugin. Zero breaking changes.

---

## 13. Competitive Positioning

```
                    Free Fields  │ Cond. Logic │ Multi-Step │ File Upload │ Stripe
────────────────────────────────┼─────────────┼────────────┼─────────────┼────────
WPForms Lite            8       │     ✗       │     ✗      │     ✗       │  3% fee
Contact Form 7          6       │     ✗       │     ✗      │     ✓       │    ✗
Fluent Forms Free      30+      │     ✓*      │     ✗      │     ✓       │    ✗
Forminator Free        13       │     ✓       │     ✓      │     ✓       │    ✓
────────────────────────────────┼─────────────┼────────────┼─────────────┼────────
GenForm Free           10       │     ✗       │     ✗      │     ✗       │    ✗
GenForm Pro            15+      │     ✓       │     ✓      │     ✓       │  0% fee
```

**Differentiators:**

- **Zero transaction fee** on Stripe (WPForms charges 3%)
- **Modern PHP 8.3 codebase** (most competitors are PHP 7.x)
- **Lightest footprint** — only loads assets when form is present
- **Beautiful builder UX** — premium feel in the free version
- **Developer-friendly** — PSR-4, namespaced, filterable

---

## 14. Revenue Projections (Conservative)

| Metric                   | Month 3  | Month 6  | Month 12 |
|--------------------------|----------|----------|----------|
| Free installs            | 500      | 2,000    | 5,000    |
| Free → Pro conversion    | 2%       | 2.5%     | 3%       |
| Pro customers            | 10       | 50       | 150      |
| Avg. revenue per user    | $79      | $89      | $99      |
| Monthly recurring (ARR)  | $790     | $4,450   | $14,850  |

---

## 15. Summary

| Dimension       | Decision                                                     |
|-----------------|--------------------------------------------------------------|
| Architecture    | Separate `genform-pro` plugin, extensible via hooks          |
| Feature Gate    | Filter-based `FeatureGate` class in free plugin              |
| Upsell Points   | Builder sidebar, locked tabs, entries page, settings page    |
| Licensing       | EDD Software Licensing, 3 tiers + lifetime                   |
| Database        | 2 new tables (`genform_files`, `genform_payments`)           |
| MVP Pro         | Conditional Logic + Multi-Step + File Upload + Stripe        |
| Timeline        | 14 weeks to launch-ready                                     |
| Pricing         | $49 / $99 / $149 annual; $349 lifetime                      |
| Free changes    | ~150 lines added, zero breaking changes                      |

This architecture ensures the free plugin remains clean, the Pro plugin
is modular, and the upgrade path is seamless. The free version impresses;
the Pro version converts.
