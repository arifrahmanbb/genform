# GenForm Free — Product Manager Audit & Polish Plan

> **Role:** Product Manager / UX Lead  
> **Audit Date:** February 18, 2026  
> **Version Audited:** 1.1.0  
> **Goal:** Polish the free version to a "wow this is free?" level  

---

## 1. Executive Assessment

GenForm v1.1.0 is functional and well-architected. However, comparing it to
the top free competitors (Fluent Forms Free, WPForms Lite, Forminator), there
are **concrete gaps** that an experienced user will notice immediately.

The improvements below are organized by **impact on first impression** and
**competitive parity** — not engineering complexity.

### Score Card (Current State)

| Area                    | Score | Gap                                      |
|-------------------------|-------|------------------------------------------|
| Builder UX              | 7/10  | Width selector is limited; no Help Text  |
| Field Settings          | 6/10  | Missing description, min/max, rows       |
| Field Types             | 7/10  | Missing: Hidden, Password, HTML/Content  |
| Frontend Rendering      | 8/10  | Good baseline; missing field descriptions |
| Admin Polish            | 8/10  | Modern and clean                         |
| Empty States            | 7/10  | Canvas empty state could be warmer       |
| i18n in Builder JS      | 5/10  | Hardcoded English strings in JS          |
| Accessibility (a11y)    | 6/10  | Missing aria-labels, fieldset/legend     |

---

## 2. Improvements — Builder Field Settings Panel

### 2.1 ❌ Missing: Field Description / Help Text

**Current:** Fields have Label, Meta Key, Placeholder, Default, Width, Class, Required.  
**Problem:** No way to add help text below a field (e.g., "We'll never share your email").  
**Impact:** Every competitor has this. Users will notice it's missing.

**Fix:** Add `help_text` property to fields, rendered as `<p class="gfm-field-description">` below the input.

**Builder UI addition:**

```
[ Placeholder ] [ Default Value ]
[ Help Text  ─────────────────── ]  ← NEW (full-width row)
```

### 2.2 ❌ Missing: Width Options — Only Full & Half

**Current:** Width selector offers only `100` (Full) and `50` (Half).  
**Problem:** Users can't build 3-column layouts (33%) or 1/4 + 3/4 arrangements.  
**Impact:** WPForms, Fluent Forms, and Forminator all offer 25/33/50/67/75/100.

**Fix:** Expand width options:

```
[ Full ] [ 3/4 ] [ 2/3 ] [ 1/2 ] [ 1/3 ] [ 1/4 ]
  100     75      67      50      33      25
```

### 2.3 ❌ Missing: Textarea Rows / Min-Max for Number

**Current:** All field types share the same generic settings panel.  
**Problem:** No way to control textarea height or number field min/max/step.  
**Impact:** These are basic configurability that power users expect.

**Fix:** Add type-specific settings that appear conditionally:

- **Textarea:** `rows` (default: 4, range: 2–12)
- **Number:** `min`, `max`, `step`
- **Text/Email/URL/Tel:** `min_length`, `max_length`

### 2.4 ⚠️ Improvement: "Meta Key" → "Field Name"

**Current:** The field is labeled "Meta Key" — developer jargon.  
**Problem:** Non-technical users don't understand "Meta Key".  

**Fix:** Rename to **"Field Name"** with a subtle description: `"Used in submissions and email tags"`.

### 2.5 ⚠️ Improvement: Duplicate Field Button

**Current:** Only Edit and Delete actions.  
**Problem:** Users who build long forms often want to clone a field with its settings.  

**Fix:** Add a Duplicate button (copy icon) next to the Edit button.

---

## 3. Improvements — Field Types

### 3.1 ❌ Missing: Hidden Field

**Why:** Hidden fields are essential for tracking — passing UTM parameters,
campaign IDs, page URLs. Every serious competitor includes this for free.  
**Effort:** Trivial — renders as `<input type="hidden">`, no label displayed.

### 3.2 ❌ Missing: Password Field

**Why:** Registration and login forms need it. It's a basic HTML input type.  
**Effort:** Trivial — renders as `<input type="password">`.

### 3.3 ⚠️ Consider: HTML/Content Block (Rich Text Block)

**Why:** Users want to add headings, paragraphs, or dividers between fields
to create sections within a form. Fluent Forms and Forminator offer this free.  
**Effort:** Low — renders raw HTML (sanitized via wp_kses) in the form output.

### 3.4 ⚠️ Consider: Section Divider / Heading

**Why:** Non-interactive block that adds a visual heading/line between
field groups. Improves long form readability massively.  
**Effort:** Low — just renders `<h3>` + optional `<hr>`.

---

## 4. Improvements — Builder Canvas & JS

### 4.1 ⚠️ Empty Canvas — More Engaging

**Current:**

```
[+ icon]
Add fields here.
```

**Problem:** Flat and uninspired. Doesn't guide the user.

**Fix:** Use a welcoming illustration with clearer CTA:

```
[Illustration SVG]
Start Building Your Form
Click a field type from the sidebar, or drag it onto the canvas.
```

### 4.2 ❌ Hardcoded English Strings in JS

**Current:** Multiple places in `form-builder.js` have hardcoded English:

- "Meta Key", "Placeholder", "Default", "Width", "Class", "Options"
- "Full", "Half", "Add Option", "Label", "Value"
- "Option 1", "Option 2", "New Option"

**Problem:** Breaks i18n for translated sites. WordPress.org reviewers flag this.

**Fix:** All strings must come from `window.genformBuilder.i18n`. The PHP side
already localizes some strings — we need to extend the map to cover every single
label in the builder JS.

### 4.3 ⚠️ Improvement: Field Type Icon in Settings Panel

**Current:** When field settings are expanded, there's no visual indicator
of what type the field is (beyond the small badge).

**Fix:** Add a subtle field type icon + label at the top of the settings panel:

```
🔤 Text Field Settings
──────────────────────
```

---

## 5. Improvements — Frontend Form Rendering

### 5.1 ❌ Field Description Not Rendered

Even after adding `help_text` to the builder, the frontend template
(`form-template.php`) needs to render it:

```html
<div class="gfm-form-field">
  <label class="gfm-label">Email <span class="gfm-required-mark">*</span></label>
  <div class="gfm-input-control">
    <input type="email" ...>
  </div>
  <p class="gfm-field-description">We'll never share your email.</p>  <!-- NEW -->
</div>
```

### 5.2 ⚠️ Form Rendering Uses Inline Wrapper

**Current:** Fields render as flat `<div>` elements without wrapper classes
for field type identification.

**Already implemented:** `gfm-type-{type}` class is present ✓

### 5.3 ⚠️ Multi-column Layout (CSS Grid)

**Current:** Width classes (`gfm-w-50`, `gfm-w-100`) exist but the parent
`.gfm-form` doesn't use a CSS Grid or Flexbox wrap layout.

**Fix:** The `.gfm-form-container form` should use:

```css
.gfm-form {
  display: flex;
  flex-wrap: wrap;
  gap: 0 20px;
}

.gfm-form-field {
  box-sizing: border-box;
}

.gfm-w-100 { width: 100%; }
.gfm-w-75  { width: calc(75% - 10px); }
.gfm-w-67  { width: calc(66.666% - 10px); }
.gfm-w-50  { width: calc(50% - 10px); }
.gfm-w-33  { width: calc(33.333% - 14px); }
.gfm-w-25  { width: calc(25% - 15px); }
```

### 5.4 ⚠️ Submit Button — Missing Loading Spinner

**Current:** While submitting, button text changes to "..." which feels cheap.

**Fix:** Replace with a proper CSS spinner + "Submitting..." text:

```javascript
btn.innerHTML = '<span class="gfm-spinner"></span> Submitting...';
btn.disabled = true;
```

### 5.5 ⚠️ Success Message — Fade In Animation

**Current:** Success/error messages appear instantly.  
**Fix:** Add CSS `animation: fadeInUp 0.3s ease` for polished feedback.

---

## 6. Improvements — Admin Forms List

### 6.1 ⚠️ Missing: Form Status Toggle

**Current:** Status column shows "active" but there's no way to deactivate
a form (only delete). An "Inactive" toggle would let users disable forms
without deleting them.

### 6.2 ⚠️ Missing: Last Updated Timestamp

**Current:** Only shows "Created" date. Users want to see when a form
was last modified.

---

## 7. Improvements — Entry Detail Page

### 7.1 ⚠️ Inline Styles

**Current:** The entry detail page uses inline `style` attributes on the
grid layout and sidebar. These should be moved to SCSS:

```php
// Current (entry-detail.php line 87):
style="display: grid; grid-template-columns: 1fr 320px; gap: 20px;"
```

**Fix:** Use a CSS class `.gfm-entry-detail-layout` and define the grid
in `_entries.scss`.

### 7.2 ⚠️ Print / PDF-Ready View

Consider adding a "Print Entry" button that opens a clean print layout.
This is free in Fluent Forms and helps agencies who need to file submissions.

---

## 8. Priority Implementation Matrix

### MUST DO (Before Public Release) — P0

| # | Improvement | Files Affected | Effort |
|---|-------------|---------------|--------|
| 1 | Add `help_text` field property | form-builder.js, form-template.php, frontend.scss | Medium |
| 2 | Expand width options (25/33/50/67/75/100) | form-builder.js, frontend.scss | Low |
| 3 | Add `Hidden` field type | form-builder.php, form-builder.js, form-template.php | Low |
| 4 | Fix all hardcoded strings in JS (i18n) | form-builder.js, Core.php (localize) | Medium |
| 5 | Move entry-detail inline styles to SCSS | entry-detail.php, _entries.scss | Low |
| 6 | Rename "Meta Key" → "Field Name" | form-builder.js, i18n | Trivial |
| 7 | Frontend multi-column grid (width classes) | frontend.scss | Low |

### SHOULD DO (Version 1.2) — P1

| # | Improvement | Files Affected | Effort |
|---|-------------|---------------|--------|
| 8 | Textarea rows setting | form-builder.js, form-template.php | Low |
| 9 | Number min/max/step settings | form-builder.js, form-template.php | Low |
| 10 | Duplicate field button | form-builder.js, _form-builder.scss | Low |
| 11 | Password field type | form-builder.php, form-builder.js | Trivial |
| 12 | Submit button loading spinner | frontend.js, frontend.scss | Low |
| 13 | Success message fade animation | frontend.scss | Trivial |
| 14 | Empty canvas upgrade (illustration + copy) | form-builder.js, _form-builder.scss | Low |

### NICE TO HAVE (Version 1.3) — P2

| # | Improvement | Files Affected | Effort |
|---|-------------|---------------|--------|
| 15 | HTML/Content block field | form-builder.js, form-template.php | Medium |
| 16 | Section Divider/Heading field | form-builder.js, form-template.php | Low |
| 17 | Form status toggle (active/inactive) | forms-list.php, Builder.php | Medium |
| 18 | "Last Updated" column on forms list | forms-list.php, DB migration | Medium |
| 19 | Print Entry button + print CSS | entry-detail.php, admin.scss | Low |

---

## 9. Summary — What Makes This "Wow, This Is Free?"

After implementing the P0 and P1 items, GenForm Free will have:

✅ **13 field types** (vs WPForms Lite's 8)  
✅ **Help text** on every field  
✅ **6-option width selector** (25/33/50/67/75/100)  
✅ **Type-specific settings** (textarea rows, number min/max)  
✅ **Field duplication** in the builder  
✅ **Fully translated** builder UI  
✅ **Polished submit UX** (spinner, animated success message)  
✅ **Proper multi-column** frontend layout  
✅ **Hidden field** for tracking  
✅ Everything else it already has (21+ templates, CSV export, GDPR, etc.)

This positions GenForm as the **most feature-complete free form builder**
that also happens to be the cleanest and most modern.
