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
        this.initConfirmationToggle();
    }

    initConfirmationToggle() {
        const toggle = jQuery('#gfm-con-type');
        const update = () => {
            const val = toggle.val();
            jQuery('.gfm-con-field').hide();
            jQuery(`.gfm-con-${val}`).show();
        };
        toggle.on('change', update);
        update();
    }

    loadInitialData() {
        if (window.genformBuilder) {
            this.fields = window.genformBuilder.initialData?.fields || [];
            if (window.genformBuilder.initialSettings) {
                const s = window.genformBuilder.initialSettings;
                const i18n = window.genformBuilder.i18n;
                jQuery('#gfm-submit-text').val(s.submit_text || i18n.submit || 'Submit');
                jQuery('#gfm-con-type').val(s.con_type || 'message');
                jQuery('#gfm-success-message').val(s.success_message || '');
                jQuery('#gfm-error-message').val(s.error_message || '');
                jQuery('#gfm-redirect-url').val(s.redirect_url || '');
                jQuery('#gfm-submit-align').val(s.submit_align || 'left');
                jQuery('#gfm-honeypot').prop('checked', s.honeypot !== false);
                jQuery('#gfm-base-font-size').val(s.base_font_size || '16');
                jQuery('#gfm-base-font-weight').val(s.base_font_weight || '400');

                jQuery('#gfm-admin-email').val(s.admin_email || '');
                jQuery('#gfm-reply-to').val(s.reply_to || '');
                jQuery('#gfm-from-name').val(s.from_name || '');
                jQuery('#gfm-from-email').val(s.from_email || '');
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
            width: '100',
            font_size: '16',
            font_weight: '400',
            options: this.isOptionField(type) ? [
                { label: 'Option 1', value: 'option_1' },
                { label: 'Option 2', value: 'option_2' }
            ] : []
        };
        this.fields.push(field);
        this.render();
        // Open settings for new field
        setTimeout(() => {
            this.container.find(`[data-id="${field.id}"] .gfm-edit-btn`).click();
        }, 100);
    }

    getDefaultLabel(type) {
        const i18n = window.genformBuilder?.i18n || {};
        const labels = {
            text: i18n.text || 'Text Field',
            email: i18n.email || 'Email Address',
            textarea: i18n.textarea || 'Paragraph Text',
            select: i18n.select || 'Dropdown',
            radio: i18n.radio || 'Multiple Choice',
            checkbox: i18n.checkbox || 'Checkboxes',
            number: i18n.number || 'Number',
            date: i18n.date || 'Date',
            url: i18n.url || 'Website',
            tel: i18n.tel || 'Phone'
        };
        return labels[type] || i18n.newField || 'New Field';
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
        field.label = field.label || '';
        field.placeholder = field.placeholder || '';
        field.default_value = field.default_value || '';
        field.css_class = field.css_class || '';
        field.width = field.width || '100';
        field.font_size = field.font_size || '16';
        field.font_weight = field.font_weight || '400';

        const node = jQuery(`
            <div class="gfm-field-node gfm-card" data-id="${field.id}">
                <div class="gfm-field-header">
                    <span class="gfm-field-drag-handle dashicons dashicons-move"></span>
                    <span class="gfm-field-title"><strong>${field.label}</strong> <small>${field.type}</small></span>
                    <div class="gfm-field-actions">
                        <button type="button" class="gfm-edit-btn gfm-opt-btn dashicons dashicons-admin-generic" title="Settings"></button>
                        <button type="button" class="gfm-delete-btn gfm-opt-btn gfm-delete-btn-hover dashicons dashicons-trash" title="Delete"></button>
                    </div>
                </div>
                <div class="gfm-field-settings-panel" style="display:none;">
                    <div class="gfm-grid">
                        <div class="gfm-col">
                            <label>Field Label</label>
                            <input type="text" class="gfm-setter" data-prop="label" value="${field.label}">
                            <span class="gfm-setting-desc">The label shown above the input field.</span>
                        </div>
                        <div class="gfm-col">
                            <label>Meta Key (Name)</label>
                            <input type="text" class="gfm-setter" data-prop="name" value="${field.name}">
                            <span class="gfm-setting-desc">Unique identifier used for entry storage.</span>
                        </div>
                    </div>
                    <div class="gfm-grid">
                        <div class="gfm-col">
                            <label>Placeholder Text</label>
                            <input type="text" class="gfm-setter" data-prop="placeholder" value="${field.placeholder}">
                            <span class="gfm-setting-desc">Hint shown inside the empty field.</span>
                        </div>
                        <div class="gfm-col">
                            <label>Default Value</label>
                            <input type="text" class="gfm-setter" data-prop="default_value" value="${field.default_value}">
                            <span class="gfm-setting-desc">Initial value when the form loads.</span>
                        </div>
                    </div>
                    <div class="gfm-grid">
                        <div class="gfm-col">
                            <label>Field Width</label>
                            <div class="gfm-width-selector">
                                <button type="button" class="gfm-width-btn ${field.width === '100' ? 'active' : ''}" data-width="100">100%</button>
                                <button type="button" class="gfm-width-btn ${field.width === '50' ? 'active' : ''}" data-width="50">50%</button>
                                <button type="button" class="gfm-width-btn ${field.width === '33' ? 'active' : ''}" data-width="33">33%</button>
                            </div>
                            <span class="gfm-setting-desc">Control how much space the field takes.</span>
                        </div>
                        <div class="gfm-col">
                             <label>Custom CSS Class</label>
                             <input type="text" class="gfm-setter" data-prop="css_class" value="${field.css_class}">
                             <span class="gfm-setting-desc">Add custom classes for advanced styling.</span>
                        </div>
                    </div>
                    <div class="gfm-grid">
                        <div class="gfm-col">
                            <label style="margin-top: 15px;">
                                <input type="checkbox" class="gfm-setter-check" data-prop="required" ${field.required ? 'checked' : ''}> 
                                <strong>Required Field</strong>
                            </label>
                            <span class="gfm-setting-desc">Mark this field as mandatory for submission.</span>
                        </div>
                    </div>
                    ${this.renderOptionsSetter(field)}
                </div>
            </div>
        `);

        // Events
        node.find('.gfm-width-btn').on('click', (e) => {
            const btn = jQuery(e.currentTarget);
            const w = btn.data('width').toString();
            field.width = w;
            node.find('.gfm-width-btn').removeClass('active');
            btn.addClass('active');
        });

        node.find('.gfm-edit-btn').on('click', (e) => {
            e.stopPropagation();
            node.toggleClass('active');
            node.find('.gfm-field-settings-panel').slideToggle();
        });

        node.find('.gfm-delete-btn').on('click', () => {
            if (confirm('Delete this field?')) {
                this.fields = this.fields.filter(f => f.id !== field.id);
                node.fadeOut(() => this.render());
            }
        });

        node.find('.gfm-setter').on('input change', (e) => {
            const prop = jQuery(e.target).data('prop');
            field[prop] = e.target.value;
            if (prop === 'label') node.find('.gfm-field-title strong').text(field.label);
        });

        node.find('.gfm-setter-check').on('change', (e) => {
            const prop = jQuery(e.target).data('prop');
            field[prop] = e.target.checked;
        });

        // Options Events
        node.find('.gfm-add-opt-btn').on('click', () => {
            field.options.push({ label: 'New Option', value: 'new_option' });
            this.updateOptionsUI(node, field);
        });

        node.on('input', '.gfm-opt-label', (e) => {
            const index = jQuery(e.target).data('index');
            field.options[index].label = e.target.value;
        });

        node.on('input', '.gfm-opt-value', (e) => {
            const index = jQuery(e.target).data('index');
            field.options[index].value = e.target.value;
        });

        node.on('click', '.gfm-opt-remove', (e) => {
            const index = jQuery(e.currentTarget).data('index');
            field.options.splice(index, 1);
            this.updateOptionsUI(node, field);
        });

        return node;
    }

    updateOptionsUI(node, field) {
        const list = node.find('.gfm-options-list');
        list.empty();
        field.options.forEach((opt, i) => {
            list.append(`
                <div class="gfm-opt-row">
                    <span class="dashicons dashicons-menu gfm-opt-drag"></span>
                    <input type="text" class="gfm-opt-label" data-index="${i}" value="${opt.label}" placeholder="Label">
                    <input type="text" class="gfm-opt-value" data-index="${i}" value="${opt.value}" placeholder="Value">
                    <div class="gfm-opt-actions">
                        <button type="button" class="gfm-opt-btn gfm-opt-remove" data-index="${i}" title="Remove"><span class="dashicons dashicons-no"></span></button>
                    </div>
                </div>
            `);
        });
    }

    renderOptionsSetter(field) {
        if (!this.isOptionField(field.type)) return '';
        return `
            <div class="gfm-options-setter">
                <h4>Field Options</h4>
                <div class="gfm-options-list">
                    ${field.options.map((opt, i) => `
                        <div class="gfm-opt-row">
                            <span class="dashicons dashicons-menu gfm-opt-drag"></span>
                            <input type="text" class="gfm-opt-label" data-index="${i}" value="${opt.label}" placeholder="Label">
                            <input type="text" class="gfm-opt-value" data-index="${i}" value="${opt.value}" placeholder="Value">
                            <div class="gfm-opt-actions">
                                <button type="button" class="gfm-opt-btn gfm-opt-remove" data-index="${i}" title="Remove"><span class="dashicons dashicons-no"></span></button>
                            </div>
                        </div>
                    `).join('')}
                </div>
                <button type="button" class="gfm-add-opt-btn"><span class="dashicons dashicons-plus"></span> Add New Option</button>
            </div>
        `;
    }

    save() {
        const settings = {
            submit_text: jQuery('#gfm-submit-text').val(),
            con_type: jQuery('#gfm-con-type').val(),
            success_message: jQuery('#gfm-success-message').val(),
            error_message: jQuery('#gfm-error-message').val(),
            redirect_url: jQuery('#gfm-redirect-url').val(),
            submit_align: jQuery('#gfm-submit-align').val(),
            honeypot: jQuery('#gfm-honeypot').is(':checked'),
            base_font_size: jQuery('#gfm-base-font-size').val(),
            base_font_weight: jQuery('#gfm-base-font-weight').val(),
            admin_email: jQuery('#gfm-admin-email').val(),
            reply_to: jQuery('#gfm-reply-to').val(),
            from_name: jQuery('#gfm-from-name').val(),
            from_email: jQuery('#gfm-from-email').val(),
            email_subject: jQuery('#gfm-email-subject').val(),
            email_body: jQuery('#gfm-email-body').val()
        };

        this.dataInput.val(JSON.stringify({ fields: this.fields }));
        this.settingsInput.val(JSON.stringify(settings));
    }
}

jQuery(document).ready(() => new GenFormBuilder());
