/**
 * GenForm Builder JS (Vanilla JS)
 *
 * Implements the core drag-and-drop form building experience,
 * including tab management, field creation, and live configuration updates.
 */

class GenFormBuilder {
    constructor() {
        this.fields = [];
        this.counter = 0;
        this.activeTab = 'fields';
        this.container = null;
        this.dataInput = null;
        this.settingsInput = null;

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', () => this.init());
        } else {
            this.init();
        }
    }

    /**
     * Return i18n strings from server with sensible fallbacks.
     */
    get i18n() {
        return window.genformBuilder?.i18n || {};
    }

    /**
     * Resolve a single i18n string with a fallback.
     */
    t(key, fallback) {
        return this.i18n[key] || fallback;
    }

    /**
     * Initializes the builder environment and loads initial data.
     */
    init() {
        this.container = document.getElementById('gfm-fields-container');
        this.dataInput = document.getElementById('gfm-data-input');
        this.settingsInput = document.getElementById('gfm-settings-input');

        if (!this.container) return;

        this.loadInitialData();
        this.bindEvents();
        this.initSortable();
        this.initConfirmationToggle();
    }

    /**
     * Toggles between URL redirect and Success message settings in the builder.
     */
    initConfirmationToggle() {
        const toggle = document.getElementById('gfm-con-type');
        if (!toggle) return;

        const update = () => {
            const val = toggle.value;
            document.querySelectorAll('.gfm-con-field').forEach(el => el.classList.add('gfm-hidden'));
            const target = document.querySelector(`.gfm-con-${val}`);
            if (target) target.classList.remove('gfm-hidden');
        };

        toggle.addEventListener('change', update);
        update();
    }

    /**
     * Fetches form structure and global settings from the PHP localized object.
     */
    loadInitialData() {
        if (window.genformBuilder) {
            this.fields = window.genformBuilder.initialData?.fields || [];

            if (window.genformBuilder.initialSettings) {
                const initialSettings = window.genformBuilder.initialSettings;
                const mapping = {
                    'gfm-submit-text': initialSettings.gfm_submit_text || 'Submit',
                    'gfm-submit-align': initialSettings.gfm_submit_align || 'left',
                    'gfm-con-type': initialSettings.gfm_con_type || 'message',
                    'gfm-success-message': initialSettings.gfm_success_message || '',
                    'gfm-error-message': initialSettings.gfm_error_message || '',
                    'gfm-redirect-url': initialSettings.gfm_redirect_url || '',
                    'gfm-base-font-size': initialSettings.gfm_base_font_size || '16',
                    'gfm-base-font-weight': initialSettings.gfm_base_font_weight || '400',
                    'gfm-admin-email': initialSettings.gfm_admin_email || '{admin_email}',
                    'gfm-from-name': initialSettings.gfm_from_name || '',
                    'gfm-from-email': initialSettings.gfm_from_email || '',
                    'gfm-reply-to': initialSettings.gfm_reply_to || '{field_email}',
                    'gfm-email-subject': initialSettings.gfm_email_subject || '',
                    'gfm-email-body': initialSettings.gfm_email_body || '',
                    'gfm-gdpr-text': initialSettings.gfm_gdpr_text || 'I consent to having this website store my submitted information.',
                    'gfm-conf-to-field': initialSettings.gfm_conf_to_field || 'email',
                    'gfm-conf-subject': initialSettings.gfm_conf_subject || '',
                    'gfm-conf-body': initialSettings.gfm_conf_body || '',
                };

                for (const id in mapping) {
                    const el = document.getElementById(id);
                    if (el) el.value = mapping[id];
                }

                // Handle GDPR checkbox separately (checked state, not value).
                const gdprCheckbox = document.getElementById('gfm-gdpr-enabled');
                if (gdprCheckbox) gdprCheckbox.checked = !!initialSettings.gfm_gdpr_enabled;

                // Handle reCAPTCHA and confirmation email checkboxes.
                const recaptchaCheckbox = document.getElementById('gfm-recaptcha-enabled');
                if (recaptchaCheckbox) recaptchaCheckbox.checked = !!initialSettings.gfm_recaptcha_enabled;
                const confCheckbox = document.getElementById('gfm-conf-enabled');
                if (confCheckbox) confCheckbox.checked = !!initialSettings.gfm_conf_enabled;
            }
        }

        if (this.fields.length) {
            this.counter = Math.max(...this.fields.map(f => {
                const parts = f.id.split('_');
                return parts.length > 1 ? parseInt(parts[1]) : 0;
            })) || 0;
            this.render();
        }
    }

    /**
     * Bind global action buttons.
     */
    bindEvents() {
        document.querySelectorAll('.gfm-add-field').forEach(btn => {
            btn.addEventListener('click', (e) => {
                this.addField(e.currentTarget.dataset.type);
            });
        });

        const form = document.getElementById('gfm-builder-form');
        if (form) {
            form.addEventListener('submit', () => this.save());
        }

        document.querySelectorAll('.gfm-tab-link').forEach(btn => {
            btn.addEventListener('click', (e) => {
                this.switchTab(e.currentTarget.dataset.tab);
            });
        });
    }

    /**
     * Use jQuery Sortable (keeping it for now as a fallback for drag-drop logic).
     */
    initSortable() {
        if (typeof jQuery !== 'undefined' && typeof jQuery.fn.sortable !== 'undefined') {
            jQuery(this.container).sortable({
                placeholder: "gfm-sortable-placeholder",
                handle: ".gfm-field-drag-handle",
                update: () => {
                    this.updateOrder();
                }
            });
        }
    }

    /**
     * Switching tabs.
     */
    switchTab(tab) {
        this.activeTab = tab;
        document.querySelectorAll('.gfm-tab-link').forEach(el => el.classList.remove('active'));
        const activeLink = document.querySelector(`.gfm-tab-link[data-tab="${tab}"]`);
        if (activeLink) activeLink.classList.add('active');

        document.querySelectorAll('.gfm-tab-content').forEach(el => el.classList.add('gfm-hidden'));
        const activeTab = document.getElementById(`gfm-tab-${tab}`);
        if (activeTab) activeTab.classList.remove('gfm-hidden');
    }

    /**
     * Append new field.
     */
    addField(type) {
        this.counter++;
        const label = this.getDefaultLabel(type);
        const field = {
            id: `field_${this.counter}`,
            type: type,
            label: label,
            name: this.slugify(label) + '_' + this.counter,
            placeholder: this.getExamplePlaceholder(type),
            help_text: '',
            required: false,
            css_class: '',
            default_value: '',
            width: '100',
            options: this.isOptionField(type) ? [
                { label: this.t('option_1', 'Option 1'), value: 'option_1' },
                { label: this.t('option_2', 'Option 2'), value: 'option_2' }
            ] : [],
            // Type-specific defaults.
            ...(type === 'textarea' ? { rows: 4 } : {}),
            ...(type === 'number' ? { min: '', max: '', step: '' } : {}),
            ...(['text', 'email', 'url', 'tel', 'textarea'].includes(type) ? { minlength: '', maxlength: '' } : {}),
            ...(type === 'section_break' ? { description: '' } : {}),
        };

        this.fields.push(field);
        this.render();

        setTimeout(() => {
            const node = this.container.querySelector(`[data-id="${field.id}"]`);
            if (node) {
                const editBtn = node.querySelector('.gfm-edit-btn');
                if (editBtn) editBtn.click();
                node.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        }, 100);
    }

    /**
     * Duplicate a field by deep-cloning its config and assigning a new ID.
     */
    duplicateField(original) {
        this.counter++;
        const clone = JSON.parse(JSON.stringify(original));
        clone.id = `field_${this.counter}`;
        clone.name = this.slugify(clone.label) + '_' + this.counter;

        const idx = this.fields.indexOf(original);
        this.fields.splice(idx + 1, 0, clone);
        this.render();

        setTimeout(() => {
            const node = this.container.querySelector(`[data-id="${clone.id}"]`);
            if (node) {
                node.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        }, 100);

        if (window.gfmAdmin) {
            window.gfmAdmin.showNotice(this.t('field_duplicated', 'Field duplicated.'));
        }
    }

    slugify(text) {
        return text.toString().toLowerCase()
            .replace(/\s+/g, '_')
            .replace(/[^\w\-]+/g, '')
            .replace(/\-\-+/g, '_')
            .replace(/^-+/, '')
            .replace(/-+$/, '');
    }

    getDefaultLabel(type) {
        const labels = {
            text:          'Text Field',
            email:         'Email Address',
            textarea:      'Paragraph',
            select:        'Dropdown',
            radio:         'Single Choice',
            checkbox:      'Checkboxes',
            number:        'Number',
            date:          'Date',
            url:           'Website',
            tel:           'Phone Number',
            hidden:        'Hidden Field',
            password:      'Password',
            section_break: 'Section Break',
        };
        return this.t(`label_${type}`, labels[type] || 'New Field');
    }

    isOptionField(type) {
        return ['select', 'radio', 'checkbox'].includes(type);
    }

    /**
     * Returns true for types that get a stripped-down settings panel.
     */
    isMinimalField(type) {
        return type === 'hidden' || type === 'section_break';
    }

    updateOrder() {
        const sortedFields = [];
        this.container.querySelectorAll('.gfm-field-node').forEach(el => {
            const id = el.dataset.id;
            const field = this.fields.find(f => f.id === id);
            if (field) sortedFields.push(field);
        });
        this.fields = sortedFields;
    }

    render() {
        this.container.innerHTML = '';
        if (this.fields.length === 0) {
            this.container.innerHTML = `
                <div class="gfm-empty-canvas">
                    <svg width="80" height="80" viewBox="0 0 80 80" fill="none" xmlns="http://www.w3.org/2000/svg" style="opacity:0.15; margin-bottom:16px;">
                        <rect x="8" y="16" width="64" height="48" rx="6" stroke="currentColor" stroke-width="3" fill="none"/>
                        <line x1="20" y1="32" x2="60" y2="32" stroke="currentColor" stroke-width="3" stroke-linecap="round"/>
                        <line x1="20" y1="44" x2="48" y2="44" stroke="currentColor" stroke-width="3" stroke-linecap="round"/>
                        <line x1="20" y1="56" x2="36" y2="56" stroke="currentColor" stroke-width="3" stroke-linecap="round"/>
                    </svg>
                    <h2>${this.t('empty_title', 'Start Building Your Form')}</h2>
                    <p>${this.t('empty_desc', 'Click a field type from the sidebar to get started.')}</p>
                </div>`;
            return;
        }
        this.fields.forEach(f => {
            this.container.appendChild(this.createFieldNode(f));
        });
    }

    getExamplePlaceholder(type) {
        const prefix = this.t('example_prefix', 'e.g.');
        const examples = {
            text: 'Type something...',
            email: 'john@example.com',
            url: 'https://yoursite.com',
            number: '123',
            tel: '+1 234 567 890',
            password: '••••••••'
        };
        return examples[type] ? `${prefix} ${examples[type]}` : '';
    }

    /**
     * Render type-specific settings (textarea rows, number min/max, etc.).
     */
    renderTypeSpecificSettings(f) {
        let html = '';

        if (f.type === 'textarea') {
            html += `
                <div class="gfm-grid">
                    <div class="gfm-col">
                        <label>${this.t('rows', 'Rows')}</label>
                        <input type="number" class="gfm-setter" data-prop="rows" value="${f.rows || 4}" min="2" max="20">
                    </div>
                    <div class="gfm-col"></div>
                </div>`;
        }

        if (['text', 'email', 'url', 'tel', 'textarea'].includes(f.type)) {
            html += `
                <div class="gfm-grid">
                    <div class="gfm-col">
                        <label>${this.t('min_length', 'Min Length')}</label>
                        <input type="number" class="gfm-setter" data-prop="minlength" value="${this.escape(f.minlength || '')}" min="0" placeholder="${this.t('example_prefix', 'e.g.')} 5">
                    </div>
                    <div class="gfm-col">
                        <label>${this.t('max_length', 'Max Length')}</label>
                        <input type="number" class="gfm-setter" data-prop="maxlength" value="${this.escape(f.maxlength || '')}" min="1" placeholder="${this.t('example_prefix', 'e.g.')} 200">
                    </div>
                </div>`;
        }

        if (f.type === 'number') {
            html += `
                <div class="gfm-grid">
                    <div class="gfm-col">
                        <label>${this.t('min_value', 'Min Value')}</label>
                        <input type="number" class="gfm-setter" data-prop="min" value="${this.escape(f.min || '')}">
                    </div>
                    <div class="gfm-col">
                        <label>${this.t('max_value', 'Max Value')}</label>
                        <input type="number" class="gfm-setter" data-prop="max" value="${this.escape(f.max || '')}">
                    </div>
                </div>
                <div class="gfm-grid">
                    <div class="gfm-col">
                        <label>${this.t('step', 'Step')}</label>
                        <input type="number" class="gfm-setter" data-prop="step" value="${this.escape(f.step || '')}" placeholder="${this.t('example_prefix', 'e.g.')} 0.01">
                    </div>
                    <div class="gfm-col"></div>
                </div>`;
        }

        return html;
    }

    /**
     * Width option data.
     */
    getWidthOptions() {
        return [
            { value: '100', label: this.t('width_full', 'Full') },
            { value: '75', label: '3/4' },
            { value: '67', label: '2/3' },
            { value: '50', label: '1/2' },
            { value: '33', label: '1/3' },
            { value: '25', label: '1/4' },
        ];
    }

    createFieldNode(f) {
        const div = document.createElement('div');
        div.className = 'gfm-field-node gfm-card';
        div.dataset.id = f.id;

        const isHidden = f.type === 'hidden';
        const fieldIcon = this.getFieldIcon(f.type);

        const widthButtons = this.getWidthOptions().map(opt =>
            `<button type="button" class="gfm-width-btn ${f.width === opt.value ? 'active' : ''}" data-width="${opt.value}">${opt.label}</button>`
        ).join('');

        // Build settings panel — hidden and section_break get minimal panels.
        let settingsPanel = '';
        const isSectionBreak = f.type === 'section_break';

        if (isHidden) {
            settingsPanel = `
                <div class="gfm-field-settings-panel gfm-hidden">
                    <div class="gfm-grid">
                        <div class="gfm-col">
                            <label>${this.t('label', 'Label')}</label>
                            <input type="text" class="gfm-setter" data-prop="label" value="${this.escape(f.label)}">
                        </div>
                        <div class="gfm-col">
                            <label>${this.t('field_name', 'Field Name')}</label>
                            <input type="text" class="gfm-setter" data-prop="name" value="${this.escape(f.name)}">
                        </div>
                    </div>
                    <div class="gfm-grid">
                        <div class="gfm-col">
                            <label>${this.t('default_value', 'Default Value')}</label>
                            <input type="text" class="gfm-setter" data-prop="default_value" value="${this.escape(f.default_value)}">
                            <span class="gfm-setting-desc">${this.t('hidden_desc', 'This value is sent with the form but not visible to users.')}</span>
                        </div>
                    </div>
                </div>`;
        } else if (isSectionBreak) {
            settingsPanel = `
                <div class="gfm-field-settings-panel gfm-hidden">
                    <div class="gfm-field-settings-header">
                        <span class="dashicons dashicons-minus"></span>
                        <span>Section Break Settings</span>
                    </div>
                    <div class="gfm-grid">
                        <div class="gfm-col" style="grid-column: span 2;">
                            <label>${this.t('section_title', 'Section Title')}</label>
                            <input type="text" class="gfm-setter" data-prop="label" value="${this.escape(f.label)}" placeholder="e.g. Personal Information">
                        </div>
                    </div>
                    <div class="gfm-grid">
                        <div class="gfm-col" style="grid-column: span 2;">
                            <label>${this.t('section_desc', 'Description')} <small style="font-weight:400;color:#64748b;">(optional)</small></label>
                            <input type="text" class="gfm-setter" data-prop="description" value="${this.escape(f.description || '')}" placeholder="e.g. Fill in your contact details below.">
                        </div>
                    </div>
                    <div class="gfm-grid">
                        <div class="gfm-col">
                            <label>${this.t('width', 'Width')}</label>
                            <div class="gfm-width-selector">
                                ${widthButtons}
                            </div>
                        </div>
                        <div class="gfm-col">
                            <label>${this.t('css_class', 'CSS Class')}</label>
                            <input type="text" class="gfm-setter" data-prop="css_class" value="${this.escape(f.css_class)}">
                        </div>
                    </div>
                </div>`;
        } else {
            settingsPanel = `
                <div class="gfm-field-settings-panel gfm-hidden">
                    <div class="gfm-field-settings-header">
                        <span class="dashicons dashicons-${fieldIcon}"></span>
                        <span>${this.escape(this.getDefaultLabel(f.type))} ${this.t('settings', 'Settings')}</span>
                    </div>
                    <div class="gfm-grid">
                        <div class="gfm-col">
                            <label>${this.t('label', 'Label')}</label>
                            <input type="text" class="gfm-setter" data-prop="label" value="${this.escape(f.label)}">
                        </div>
                        <div class="gfm-col">
                            <label>${this.t('field_name', 'Field Name')}</label>
                            <input type="text" class="gfm-setter" data-prop="name" value="${this.escape(f.name)}">
                            <span class="gfm-setting-desc">${this.t('field_name_desc', 'Used in submissions and email tags.')}</span>
                        </div>
                    </div>
                    <div class="gfm-grid">
                        <div class="gfm-col">
                            <label>${this.t('placeholder', 'Placeholder')}</label>
                            <input type="text" class="gfm-setter" data-prop="placeholder" value="${this.escape(f.placeholder)}">
                        </div>
                        <div class="gfm-col">
                            <label>${this.t('default_value', 'Default Value')}</label>
                            <input type="text" class="gfm-setter" data-prop="default_value" value="${this.escape(f.default_value)}">
                        </div>
                    </div>
                    <div class="gfm-grid">
                        <div class="gfm-col" style="grid-column: span 2;">
                            <label>${this.t('help_text', 'Help Text')}</label>
                            <input type="text" class="gfm-setter" data-prop="help_text" value="${this.escape(f.help_text || '')}" placeholder="${this.t('help_text_placeholder', 'Displayed below the field to guide the user.')}">
                        </div>
                    </div>
                    ${this.renderTypeSpecificSettings(f)}
                    <div class="gfm-grid">
                        <div class="gfm-col">
                            <label>${this.t('width', 'Width')}</label>
                            <div class="gfm-width-selector">
                                ${widthButtons}
                            </div>
                        </div>
                        <div class="gfm-col">
                            <label>${this.t('css_class', 'CSS Class')}</label>
                            <input type="text" class="gfm-setter" data-prop="css_class" value="${this.escape(f.css_class)}">
                        </div>
                    </div>
                    <div class="gfm-grid">
                        <div class="gfm-col">
                            <div class="gfm-required-toggle-wrap">
                                <label class="gfm-switch">
                                    <input type="checkbox" class="gfm-setter-check" data-prop="required" ${f.required ? 'checked' : ''}>
                                    <span class="slider"></span>
                                </label>
                                <span class="gfm-required-label">${this.t('required', 'Required')}</span>
                            </div>
                        </div>
                    </div>
                    ${this.renderOptionsSetter(f)}
                </div>`;
        }

        div.innerHTML = `
            <div class="gfm-field-header">
                <span class="gfm-field-drag-handle dashicons dashicons-move"></span>
                <span class="gfm-field-title"><strong>${this.escape(f.label)}</strong> <small>${this.escape(f.type)}</small></span>
                <div class="gfm-field-actions">
                    <button type="button" class="gfm-duplicate-btn gfm-opt-btn" title="${this.t('duplicate', 'Duplicate')}"><span class="dashicons dashicons-admin-page"></span></button>
                    <button type="button" class="gfm-edit-btn gfm-opt-btn" title="${this.t('edit', 'Edit')}"><span class="dashicons dashicons-admin-generic"></span></button>
                    <button type="button" class="gfm-delete-btn gfm-opt-btn" title="${this.t('delete', 'Delete')}"><span class="dashicons dashicons-trash"></span></button>
                </div>
            </div>
            ${settingsPanel}`;

        // Event: Width
        div.querySelectorAll('.gfm-width-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                f.width = btn.dataset.width;
                div.querySelectorAll('.gfm-width-btn').forEach(b => b.classList.remove('active'));
                btn.classList.add('active');
            });
        });

        // Event: Edit
        div.querySelector('.gfm-edit-btn').addEventListener('click', (e) => {
            e.stopPropagation();
            const panel = div.querySelector('.gfm-field-settings-panel');
            const isActive = div.classList.toggle('active');

            if (isActive) {
                panel.classList.remove('gfm-hidden');
            } else {
                panel.classList.add('gfm-hidden');
            }
        });

        // Event: Duplicate
        div.querySelector('.gfm-duplicate-btn').addEventListener('click', (e) => {
            e.stopPropagation();
            this.duplicateField(f);
        });

        // Event: Delete
        div.querySelector('.gfm-delete-btn').addEventListener('click', async () => {
            const confirmed = await window.gfmAdmin.gfmConfirm(
                this.t('delete_field_title', 'Delete Field?'),
                this.t('delete_field_desc', 'This field and its configuration will be removed from the builder.')
            );
            if (confirmed) {
                window.gfmAdmin.showSpinner();
                setTimeout(() => {
                    this.fields = this.fields.filter(x => x.id !== f.id);
                    div.classList.add('gfm-removing');
                    setTimeout(() => {
                        this.render();
                        window.gfmAdmin.hideSpinner();
                        window.gfmAdmin.showNotice(this.t('field_removed', 'Field removed successfully.'));
                    }, 300);
                }, 500);
            }
        });

        // Sync inputs
        div.querySelectorAll('.gfm-setter').forEach(input => {
            input.addEventListener('input', (e) => {
                const prop = e.target.dataset.prop;
                f[prop] = e.target.value;
                if (prop === 'label') {
                    div.querySelector('.gfm-field-title strong').textContent = f.label;
                }
            });
        });

        div.querySelectorAll('.gfm-setter-check').forEach(input => {
            input.addEventListener('change', (e) => {
                f[e.target.dataset.prop] = e.target.checked;
            });
        });

        // Options Events
        const addOptBtn = div.querySelector('.gfm-add-opt-btn');
        if (addOptBtn) {
            addOptBtn.addEventListener('click', () => {
                f.options.push({ label: this.t('new_option', 'New Option'), value: 'opt' });
                this.updateOptionsUI(div, f);
            });
        }

        div.addEventListener('input', (e) => {
            if (e.target.classList.contains('gfm-opt-label')) {
                f.options[e.target.dataset.index].label = e.target.value;
            }
            if (e.target.classList.contains('gfm-opt-value')) {
                f.options[e.target.dataset.index].value = e.target.value;
            }
        });

        div.addEventListener('click', (e) => {
            const removeBtn = e.target.closest('.gfm-opt-remove');
            if (removeBtn) {
                f.options.splice(removeBtn.dataset.index, 1);
                this.updateOptionsUI(div, f);
            }
        });

        return div;
    }

    /**
     * Get the dashicon name for a field type.
     */
    getFieldIcon(type) {
        const icons = {
            text:          'edit',
            email:         'email',
            textarea:      'text',
            number:        'calculator',
            select:        'menu-alt',
            radio:         'marker',
            checkbox:      'yes',
            date:          'calendar-alt',
            url:           'admin-links',
            tel:           'phone',
            hidden:        'hidden',
            password:      'lock',
            section_break: 'minus',
        };
        return icons[type] || 'admin-generic';
    }

    updateOptionsUI(node, f) {
        const list = node.querySelector('.gfm-options-list');
        if (!list) return;
        list.innerHTML = '';
        f.options.forEach((o, i) => {
            const row = document.createElement('div');
            row.className = 'gfm-opt-row';
            row.innerHTML = `
                <span class="dashicons dashicons-menu gfm-opt-drag"></span>
                <input type="text" class="gfm-opt-label" data-index="${i}" value="${this.escape(o.label)}" placeholder="${this.t('opt_label', 'Label')}">
                <input type="text" class="gfm-opt-value" data-index="${i}" value="${this.escape(o.value)}" placeholder="${this.t('opt_value', 'Value')}">
                <button type="button" class="gfm-opt-btn gfm-opt-remove" data-index="${i}">
                    <span class="dashicons dashicons-no-alt"></span>
                </button>`;
            list.appendChild(row);
        });
    }

    renderOptionsSetter(f) {
        if (!this.isOptionField(f.type)) return '';
        const listHtml = f.options.map((o, i) => `
            <div class="gfm-opt-row">
                <span class="dashicons dashicons-menu gfm-opt-drag"></span>
                <input type="text" class="gfm-opt-label" data-index="${i}" value="${this.escape(o.label)}" placeholder="${this.t('opt_label', 'Label')}">
                <input type="text" class="gfm-opt-value" data-index="${i}" value="${this.escape(o.value)}" placeholder="${this.t('opt_value', 'Value')}">
                <button type="button" class="gfm-opt-btn gfm-opt-remove" data-index="${i}">
                    <span class="dashicons dashicons-no-alt"></span>
                </button>
            </div>`).join('');

        return `
            <div class="gfm-options-setter">
                <h4>${this.t('options_title', 'Options')}</h4>
                <div class="gfm-options-list">${listHtml}</div>
                <button type="button" class="gfm-add-opt-btn">
                    <span class="dashicons dashicons-plus-alt2"></span>
                    ${this.t('add_option', 'Add Option')}
                </button>
            </div>`;
    }

    save() {
        const settings = {
            gfm_submit_text: document.getElementById('gfm-submit-text')?.value,
            gfm_submit_align: document.getElementById('gfm-submit-align')?.value,
            gfm_con_type: document.getElementById('gfm-con-type')?.value,
            gfm_success_message: document.getElementById('gfm-success-message')?.value,
            gfm_error_message: document.getElementById('gfm-error-message')?.value,
            gfm_redirect_url: document.getElementById('gfm-redirect-url')?.value,
            gfm_base_font_size: document.getElementById('gfm-base-font-size')?.value,
            gfm_base_font_weight: document.getElementById('gfm-base-font-weight')?.value,
            gfm_admin_email: document.getElementById('gfm-admin-email')?.value,
            gfm_from_name: document.getElementById('gfm-from-name')?.value,
            gfm_from_email: document.getElementById('gfm-from-email')?.value,
            gfm_reply_to: document.getElementById('gfm-reply-to')?.value,
            gfm_email_subject: document.getElementById('gfm-email-subject')?.value,
            gfm_email_body: document.getElementById('gfm-email-body')?.value,
            gfm_gdpr_enabled: document.getElementById('gfm-gdpr-enabled')?.checked ? '1' : '',
            gfm_gdpr_text: document.getElementById('gfm-gdpr-text')?.value,
            gfm_recaptcha_enabled: document.getElementById('gfm-recaptcha-enabled')?.checked ? '1' : '',
            gfm_conf_enabled: document.getElementById('gfm-conf-enabled')?.checked ? '1' : '',
            gfm_conf_to_field: document.getElementById('gfm-conf-to-field')?.value,
            gfm_conf_subject: document.getElementById('gfm-conf-subject')?.value,
            gfm_conf_body: document.getElementById('gfm-conf-body')?.value,
        };

        if (this.dataInput) this.dataInput.value = JSON.stringify({ fields: this.fields });
        if (this.settingsInput) this.settingsInput.value = JSON.stringify(settings);
    }

    escape(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
}

new GenFormBuilder();

// ============================================================
// Template Tag Picker — click a chip to insert it at the cursor
// position of the related textarea/input.
// ============================================================
document.addEventListener('click', function (e) {
    const chip = e.target.closest('.gfm-tag-chip');
    if (!chip) return;
    e.preventDefault();

    const picker = chip.closest('.gfm-tag-picker');
    if (!picker) return;
    const targetSel = picker.getAttribute('data-target');
    const target = targetSel ? document.querySelector(targetSel) : null;
    if (!target) return;

    const tag = chip.getAttribute('data-tag') || chip.textContent.trim();
    const start = target.selectionStart ?? target.value.length;
    const end = target.selectionEnd ?? target.value.length;
    target.value = target.value.slice(0, start) + tag + target.value.slice(end);

    target.focus();
    const caret = start + tag.length;
    if (typeof target.setSelectionRange === 'function') {
        target.setSelectionRange(caret, caret);
    }
    target.dispatchEvent(new Event('input', { bubbles: true }));

    chip.classList.add('is-inserted');
    setTimeout(() => chip.classList.remove('is-inserted'), 600);
});
