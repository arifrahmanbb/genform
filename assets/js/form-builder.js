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
                const s = window.genformBuilder.initialSettings;
                const mapping = {
                    'gfm-submit-text': s.submit_text || 'Submit',
                    'gfm-submit-align': s.submit_align || 'left',
                    'gfm-con-type': s.con_type || 'message',
                    'gfm-success-message': s.success_message || '',
                    'gfm-error-message': s.error_message || '',
                    'gfm-redirect-url': s.redirect_url || '',
                    'gfm-base-font-size': s.base_font_size || '16',
                    'gfm-base-font-weight': s.base_font_weight || '400',
                    'gfm-admin-email': s.admin_email || '{admin_email}',
                    'gfm-from-name': s.from_name || '',
                    'gfm-from-email': s.from_email || '',
                    'gfm-reply-to': s.reply_to || '{field_email}',
                    'gfm-email-subject': s.email_subject || '',
                    'gfm-email-body': s.email_body || ''
                };

                for (const id in mapping) {
                    const el = document.getElementById(id);
                    if (el) el.value = mapping[id];
                }
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
            required: false,
            css_class: '',
            default_value: '',
            width: '100',
            options: this.isOptionField(type) ? [
                { label: 'Option 1', value: 'option_1' },
                { label: 'Option 2', value: 'option_2' }
            ] : []
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
            text: 'Text Field',
            email: 'Email Address',
            textarea: 'Paragraph',
            select: 'Dropdown',
            radio: 'Single Choice',
            checkbox: 'Checkboxes',
            number: 'Number',
            date: 'Date',
            url: 'Website',
            tel: 'Phone Number'
        };
        return labels[type] || 'New Field';
    }

    isOptionField(type) {
        return ['select', 'radio', 'checkbox'].includes(type);
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
                    <span class="dashicons dashicons-plus-alt"></span>
                    <p>Add fields here.</p>
                </div>`;
            return;
        }
        this.fields.forEach(f => {
            this.container.appendChild(this.createFieldNode(f));
        });
    }

    getExamplePlaceholder(type) {
        const examples = {
            text: 'Type something...',
            email: 'john@example.com',
            url: 'https://yoursite.com',
            number: '123',
            tel: '+1 234 567 890'
        };
        return 'e.g. ' + (examples[type] || 'Enter value...');
    }

    createFieldNode(f) {
        const i18n = window.genformBuilder?.i18n || {};
        const div = document.createElement('div');
        div.className = 'gfm-field-node gfm-card';
        div.dataset.id = f.id;

        div.innerHTML = `
            <div class="gfm-field-header">
                <span class="gfm-field-drag-handle dashicons dashicons-move"></span>
                <span class="gfm-field-title"><strong>${this.escape(f.label)}</strong> <small>${this.escape(f.type)}</small></span>
                <div class="gfm-field-actions">
                    <button type="button" class="gfm-edit-btn gfm-opt-btn dashicons dashicons-admin-generic"></button>
                    <button type="button" class="gfm-delete-btn gfm-opt-btn dashicons dashicons-trash"></button>
                </div>
            </div>
            <div class="gfm-field-settings-panel gfm-hidden" style="display:none;">
                <div class="gfm-grid">
                    <div class="gfm-col">
                        <label>${i18n.label || 'Label'}</label>
                        <input type="text" class="gfm-setter" data-prop="label" value="${this.escape(f.label)}">
                    </div>
                    <div class="gfm-col">
                        <label>Meta Key</label>
                        <input type="text" class="gfm-setter" data-prop="name" value="${this.escape(f.name)}">
                    </div>
                </div>
                <div class="gfm-grid">
                    <div class="gfm-col">
                        <label>Placeholder</label>
                        <input type="text" class="gfm-setter" data-prop="placeholder" value="${this.escape(f.placeholder)}">
                    </div>
                    <div class="gfm-col">
                        <label>Default</label>
                        <input type="text" class="gfm-setter" data-prop="default_value" value="${this.escape(f.default_value)}">
                    </div>
                </div>
                <div class="gfm-grid">
                    <div class="gfm-col">
                        <label>Width</label>
                        <div class="gfm-width-selector">
                            <button type="button" class="gfm-width-btn ${f.width === '100' ? 'active' : ''}" data-width="100">Full</button>
                            <button type="button" class="gfm-width-btn ${f.width === '50' ? 'active' : ''}" data-width="50">Half</button>
                        </div>
                    </div>
                    <div class="gfm-col">
                        <label>Class</label>
                        <input type="text" class="gfm-setter" data-prop="css_class" value="${this.escape(f.css_class)}">
                    </div>
                </div>
                <div class="gfm-grid">
                    <div class="gfm-col">
                        <label class="gfm-checkbox-trigger">
                            <input type="checkbox" class="gfm-setter-check" data-prop="required" ${f.required ? 'checked' : ''}>
                            <span>${i18n.required || 'Required'}</span>
                        </label>
                    </div>
                </div>
                ${this.renderOptionsSetter(f)}
            </div>`;

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
                panel.style.display = 'block';
                panel.classList.remove('gfm-hidden');
            } else {
                panel.style.display = 'none';
                panel.classList.add('gfm-hidden');
            }
        });

        // Event: Delete
        div.querySelector('.gfm-delete-btn').addEventListener('click', async () => {
            const confirmed = await window.gfmAdmin.gfmConfirm('Delete Field?', 'This field and its configuration will be removed from the builder.');
            if (confirmed) {
                window.gfmAdmin.showSpinner();
                setTimeout(() => {
                    this.fields = this.fields.filter(x => x.id !== f.id);
                    div.style.transition = 'opacity 0.3s';
                    div.style.opacity = '0';
                    setTimeout(() => {
                        this.render();
                        window.gfmAdmin.hideSpinner();
                        window.gfmAdmin.showNotice('Field removed successfully.');
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
                f.options.push({ label: 'New Option', value: 'opt' });
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

    updateOptionsUI(node, f) {
        const list = node.querySelector('.gfm-options-list');
        if (!list) return;
        list.innerHTML = '';
        f.options.forEach((o, i) => {
            const row = document.createElement('div');
            row.className = 'gfm-opt-row';
            row.innerHTML = `
                <span class="dashicons dashicons-menu gfm-opt-drag"></span>
                <input type="text" class="gfm-opt-label" data-index="${i}" value="${this.escape(o.label)}">
                <input type="text" class="gfm-opt-value" data-index="${i}" value="${this.escape(o.value)}">
                <button type="button" class="gfm-opt-btn gfm-opt-remove" data-index="${i}">
                    <span class="dashicons dashicons-no"></span>
                </button>`;
            list.appendChild(row);
        });
    }

    renderOptionsSetter(f) {
        if (!this.isOptionField(f.type)) return '';
        const listHtml = f.options.map((o, i) => `
            <div class="gfm-opt-row">
                <span class="dashicons dashicons-menu gfm-opt-drag"></span>
                <input type="text" class="gfm-opt-label" data-index="${i}" value="${this.escape(o.label)}">
                <input type="text" class="gfm-opt-value" data-index="${i}" value="${this.escape(o.value)}">
                <button type="button" class="gfm-opt-btn gfm-opt-remove" data-index="${i}">
                    <span class="dashicons dashicons-no"></span>
                </button>
            </div>`).join('');

        return `
            <div class="gfm-options-setter">
                <h4>Options</h4>
                <div class="gfm-options-list">${listHtml}</div>
                <button type="button" class="gfm-add-opt-btn">Add Option</button>
            </div>`;
    }

    save() {
        const settings = {
            submit_text: document.getElementById('gfm-submit-text')?.value,
            submit_align: document.getElementById('gfm-submit-align')?.value,
            con_type: document.getElementById('gfm-con-type')?.value,
            success_message: document.getElementById('gfm-success-message')?.value,
            error_message: document.getElementById('gfm-error-message')?.value,
            redirect_url: document.getElementById('gfm-redirect-url')?.value,
            base_font_size: document.getElementById('gfm-base-font-size')?.value,
            base_font_weight: document.getElementById('gfm-base-font-weight')?.value,
            admin_email: document.getElementById('gfm-admin-email')?.value,
            from_name: document.getElementById('gfm-from-name')?.value,
            from_email: document.getElementById('gfm-from-email')?.value,
            reply_to: document.getElementById('gfm-reply-to')?.value,
            email_subject: document.getElementById('gfm-email-subject')?.value,
            email_body: document.getElementById('gfm-email-body')?.value,
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
