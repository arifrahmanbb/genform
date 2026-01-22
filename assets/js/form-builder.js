/**
 * GenForm Builder JS
 * Market-standard Drag & Drop implementation
 */
class GenFormBuilder {
    constructor() {
        this.fields = [];
        this.counter = 0;
        this.activeTab = 'fields';
        this.init();
    }

    init() {
        this.container = jQuery('#gfm-fields-container');
        this.dataInput = jQuery('#gfm-data-input');
        this.settingsInput = jQuery('#gfm-settings-input');

        this.loadInitialData();
        this.bindEvents();
        this.initSortable();
    }

    loadInitialData() {
        if (window.genformBuilder) {
            this.fields = window.genformBuilder.initialData?.fields || [];
            // Sync settings if they exist
            if (window.genformBuilder.initialSettings) {
                const s = window.genformBuilder.initialSettings;
                jQuery('#gfm-submit-text').val(s.submit_text || 'Submit');
                jQuery('#gfm-success-message').val(s.success_message || 'Thank you!');
                jQuery('#gfm-redirect-url').val(s.redirect_url || '');
                jQuery('#gfm-admin-email').val(s.admin_email || '');
                jQuery('#gfm-email-subject').val(s.email_subject || '');
                jQuery('#gfm-email-body').val(s.email_body || '');
            }
        }

        if (this.fields.length) {
            this.counter = Math.max(...this.fields.map(f => parseInt(f.id.split('_')[1]))) || 0;
            this.render();
        }
    }

    bindEvents() {
        jQuery('.gfm-add-field').on('click', (e) => {
            const type = jQuery(e.currentTarget).data('type');
            this.addField(type);
        });

        jQuery('#gfm-builder-form').on('submit', () => this.save());

        // Tab switching
        jQuery('.gfm-tab-link').on('click', (e) => {
            const tab = jQuery(e.currentTarget).data('tab');
            this.switchTab(tab);
        });
    }

    initSortable() {
        this.container.sortable({
            placeholder: "gfm-sortable-placeholder",
            handle: ".gfm-field-drag-handle",
            update: () => {
                this.updateOrder();
            }
        });
    }

    switchTab(tab) {
        this.activeTab = tab;
        jQuery('.gfm-tab-link').removeClass('active');
        jQuery(`.gfm-tab-link[data-tab="${tab}"]`).addClass('active');
        jQuery('.gfm-tab-content').hide();
        jQuery(`#gfm-tab-${tab}`).show();
    }

    addField(type) {
        this.counter++;
        const field = {
            id: `field_${this.counter}`,
            type: type,
            label: this.getDefaultLabel(type),
            name: `field_${this.counter}`,
            placeholder: '',
            required: false,
            css_class: '',
            default_value: '',
            options: this.isOptionField(type) ? [
                { label: 'Option 1', value: 'option_1' },
                { label: 'Option 2', value: 'option_2' }
            ] : []
        };
        this.fields.push(field);
        this.render();
    }

    getDefaultLabel(type) {
        const labels = {
            text: 'Text Field',
            email: 'Email Address',
            textarea: 'Paragraph Text',
            select: 'Dropdown',
            radio: 'Multiple Choice',
            checkbox: 'Checkboxes',
            number: 'Number',
            date: 'Date',
            url: 'Website',
            tel: 'Phone'
        };
        return labels[type] || 'New Field';
    }

    isOptionField(type) {
        return ['select', 'radio', 'checkbox'].includes(type);
    }

    updateOrder() {
        const newFields = [];
        this.container.find('.gfm-field-node').each((i, el) => {
            const id = jQuery(el).data('id');
            const field = this.fields.find(f => f.id === id);
            if (field) newFields.push(field);
        });
        this.fields = newFields;
    }

    render() {
        this.container.empty();
        this.fields.forEach(field => {
            const node = this.createFieldNode(field);
            this.container.append(node);
        });
    }

    createFieldNode(field) {
        const node = jQuery(`
            <div class="gfm-field-node gfm-card" data-id="${field.id}">
                <div class="gfm-field-header">
                    <span class="gfm-field-drag-handle dashicons dashicons-move"></span>
                    <span class="gfm-field-title"><strong>${field.label}</strong> <small>(${field.type})</small></span>
                    <div class="gfm-field-actions">
                        <button type="button" class="gfm-edit-btn dashicons dashicons-admin-generic" title="Settings"></button>
                        <button type="button" class="gfm-delete-btn dashicons dashicons-trash" title="Delete"></button>
                    </div>
                </div>
                <div class="gfm-field-settings-panel" style="display:none;">
                    <div class="gfm-grid">
                        <div class="gfm-col">
                            <label>Label</label>
                            <input type="text" class="gfm-setter" data-prop="label" value="${field.label}">
                        </div>
                        <div class="gfm-col">
                            <label>Name (Meta Key)</label>
                            <input type="text" class="gfm-setter" data-prop="name" value="${field.name}">
                        </div>
                    </div>
                    <div class="gfm-grid">
                        <div class="gfm-col">
                            <label>Placeholder</label>
                            <input type="text" class="gfm-setter" data-prop="placeholder" value="${field.placeholder}">
                        </div>
                        <div class="gfm-col">
                            <label>Default Value</label>
                            <input type="text" class="gfm-setter" data-prop="default_value" value="${field.default_value}">
                        </div>
                    </div>
                    <div class="gfm-grid">
                        <div class="gfm-col">
                            <label><input type="checkbox" class="gfm-setter-check" data-prop="required" ${field.required ? 'checked' : ''}> Required</label>
                        </div>
                        <div class="gfm-col">
                            <label>CSS Class</label>
                            <input type="text" class="gfm-setter" data-prop="css_class" value="${field.css_class}">
                        </div>
                    </div>
                    ${this.renderOptionsSetter(field)}
                </div>
            </div>
        `);

        // Events
        node.find('.gfm-edit-btn').on('click', () => {
            node.find('.gfm-field-settings-panel').slideToggle();
        });

        node.find('.gfm-delete-btn').on('click', () => {
            this.fields = this.fields.filter(f => f.id !== field.id);
            node.fadeOut(() => this.render());
        });

        node.find('.gfm-setter').on('input', (e) => {
            const prop = jQuery(e.target).data('prop');
            field[prop] = e.target.value;
            if (prop === 'label') node.find('.gfm-field-title strong').text(field.label);
        });

        node.find('.gfm-setter-check').on('change', (e) => {
            const prop = jQuery(e.target).data('prop');
            field[prop] = e.target.checked;
        });

        return node;
    }

    renderOptionsSetter(field) {
        if (!this.isOptionField(field.type)) return '';
        return `
            <div class="gfm-options-setter">
                <label>Options</label>
                <div class="gfm-options-list">
                    ${field.options.map((opt, i) => `
                        <div class="gfm-opt-row">
                            <input type="text" class="gfm-opt-label" data-index="${i}" value="${opt.label}" placeholder="Label">
                            <input type="text" class="gfm-opt-value" data-index="${i}" value="${opt.value}" placeholder="Value">
                        </div>
                    `).join('')}
                </div>
            </div>
        `;
    }

    save() {
        const settings = {
            submit_text: jQuery('#gfm-submit-text').val(),
            success_message: jQuery('#gfm-success-message').val(),
            redirect_url: jQuery('#gfm-redirect-url').val(),
            admin_email: jQuery('#gfm-admin-email').val(),
            email_subject: jQuery('#gfm-email-subject').val(),
            email_body: jQuery('#gfm-email-body').val()
        };

        this.dataInput.val(JSON.stringify({ fields: this.fields }));
        this.settingsInput.val(JSON.stringify(settings));
    }
}

jQuery(document).ready(() => new GenFormBuilder());
