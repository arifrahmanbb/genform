/**
 * GenForm Builder JS
 *
 * Implements the core drag-and-drop form building experience,
 * including tab management, field creation, and live configuration updates.
 */

class GenFormBuilder {
    constructor() {
        this.fields = [];
        this.counter = 0;
        this.activeTab = 'fields';
        this.init();
    }

    /**
     * Initializes the builder environment and loads initial data.
     */
    init() {
        this.container = jQuery('#gfm-fields-container');
        this.dataInput = jQuery('#gfm-data-input');
        this.settingsInput = jQuery('#gfm-settings-input');

        this.loadInitialData();
        this.bindEvents();
        this.initSortable();
        this.initConfirmationToggle();
    }

    /**
     * Toggles between URL redirect and Success message settings in the builder.
     */
    initConfirmationToggle() {
        const toggle = jQuery('#gfm-con-type');
        const update = () => {
            const val = toggle.val();
            jQuery('.gfm-con-field').addClass('gfm-hidden');
            jQuery(`.gfm-con-${val}`).removeClass('gfm-hidden');
        };
        toggle.on('change', update);
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
                jQuery('#gfm-submit-text').val(s.submit_text || 'Submit');
                jQuery('#gfm-con-type').val(s.con_type || 'message');
                jQuery('#gfm-success-message').val(s.success_message || '');
                jQuery('#gfm-error-message').val(s.error_message || '');
                jQuery('#gfm-redirect-url').val(s.redirect_url || '');
                jQuery('#gfm-admin-email').val(s.admin_email || '');
                jQuery('#gfm-from-name').val(s.from_name || '');
            }
        }

        if (this.fields.length) {
            // Find the highest field ID suffix to prevent duplication.
            this.counter = Math.max(...this.fields.map(f => parseInt(f.id.split('_')[1]))) || 0;
            this.render();
        }
    }

    /**
     * Bind global action buttons like Add Field and Switch Tab.
     */
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

    /**
     * Integrates jQuery UI Sortable for reordering fields on the canvas.
     */
    initSortable() {
        this.container.sortable({
            placeholder: "gfm-sortable-placeholder",
            handle: ".gfm-field-drag-handle",
            update: () => {
                this.updateOrder();
            }
        });
    }

    /**
     * Logic for switching between builder navigation tabs.
     */
    switchTab(tab) {
        this.activeTab = tab;
        jQuery('.gfm-tab-link').removeClass('active');
        jQuery(`.gfm-tab-link[data-tab="${tab}"]`).addClass('active');

        jQuery('.gfm-tab-content').addClass('gfm-hidden');
        jQuery(`#gfm-tab-${tab}`).removeClass('gfm-hidden');
    }

    /**
     * Append a new field of a specific type to the internal fields collection.
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

        // Auto-open settings for the newly added field.
        setTimeout(() => {
            const $node = this.container.find(`[data-id="${field.id}"]`);
            $node.find('.gfm-edit-btn').click();
            jQuery('html, body').animate({
                scrollTop: $node.offset().top - 100
            }, 500);
        }, 100);
    }

    /**
     * Converts a display label to a database-safe meta key.
     */
    slugify(text) {
        return text.toString().toLowerCase()
            .replace(/\s+/g, '_')
            .replace(/[^\w\-]+/g, '')
            .replace(/\-\-+/g, '_')
            .replace(/^-+/, '')
            .replace(/-+$/, '');
    }

    /**
     * Provides human-readable labels for various field types.
     */
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

    /**
     * Resyncs the internal fields array with the visual order on the canvas.
     */
    updateOrder() {
        const sortedFields = [];
        this.container.find('.gfm-field-node').each((i, el) => {
            const id = jQuery(el).data('id');
            const field = this.fields.find(f => f.id === id);
            if (field) {
                sortedFields.push(field);
            }
        });
        this.fields = sortedFields;
    }

    /**
     * Clears the canvas and rebuilds every field node from existing data.
     */
    render() {
        this.container.empty();
        this.fields.forEach(f => {
            this.container.append(this.createFieldNode(f));
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

    /**
     * Generates the jQuery DOM element representing a field in the builder.
     */
    createFieldNode(f) {
        const i18n = window.genformBuilder?.i18n || {};
        f.label = f.label || '';
        f.placeholder = f.placeholder || '';
        f.default_value = f.default_value || '';
        f.css_class = f.css_class || '';
        f.width = f.width || '100';

        const node = jQuery(`
            <div class="gfm-field-node gfm-card" data-id="${f.id}">
                <div class="gfm-field-header">
                    <span class="gfm-field-drag-handle dashicons dashicons-move"></span>
                    <span class="gfm-field-title"><strong>${f.label}</strong> <small>${f.type}</small></span>
                    <div class="gfm-field-actions">
                        <button type="button" class="gfm-edit-btn gfm-opt-btn dashicons dashicons-admin-generic"></button>
                        <button type="button" class="gfm-delete-btn gfm-opt-btn dashicons dashicons-trash"></button>
                    </div>
                </div>
                <div class="gfm-field-settings-panel gfm-hidden">
                    <div class="gfm-grid">
                        <div class="gfm-col">
                            <label>${i18n.label || 'Label'}</label>
                            <input type="text" class="gfm-setter" data-prop="label" value="${f.label}">
                        </div>
                        <div class="gfm-col">
                            <label>Meta Key</label>
                            <input type="text" class="gfm-setter" data-prop="name" value="${f.name}">
                        </div>
                    </div>
                    <div class="gfm-grid">
                        <div class="gfm-col">
                            <label>Placeholder</label>
                            <input type="text" class="gfm-setter" data-prop="placeholder" value="${f.placeholder}">
                        </div>
                        <div class="gfm-col">
                            <label>Default</label>
                            <input type="text" class="gfm-setter" data-prop="default_value" value="${f.default_value}">
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
                            <input type="text" class="gfm-setter" data-prop="css_class" value="${f.css_class}">
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
                </div>
            </div>
        `);

        // Handle width toggle events.
        node.find('.gfm-width-btn').on('click', (e) => {
            const btn = jQuery(e.currentTarget);
            f.width = btn.data('width').toString();
            node.find('.gfm-width-btn').removeClass('active');
            btn.addClass('active');
        });

        // Toggle settings panel editability.
        node.find('.gfm-edit-btn').on('click', (e) => {
            e.stopPropagation();
            node.toggleClass('active');
            node.find('.gfm-field-settings-panel').slideToggle();
        });

        // Handle field deletion.
        node.find('.gfm-delete-btn').on('click', () => {
            if (confirm('Are you sure you want to delete this field?')) {
                this.fields = this.fields.filter(x => x.id !== f.id);
                node.fadeOut(() => this.render());
            }
        });

        // Sync input changes back to the internal data collection.
        node.find('.gfm-setter').on('input change', (e) => {
            const prop = jQuery(e.target).data('prop');
            f[prop] = e.target.value;
            if (prop === 'label') {
                node.find('.gfm-field-title strong').text(f.label);
            }
        });

        node.find('.gfm-setter-check').on('change', (e) => {
            const prop = jQuery(e.target).data('prop');
            f[prop] = e.target.checked;
        });

        // Custom field option management logic.
        node.find('.gfm-add-opt-btn').on('click', () => {
            f.options.push({ label: 'New Option', value: 'opt' });
            this.updateOptionsUI(node, f);
        });

        node.on('input', '.gfm-opt-label', (e) => {
            const idx = jQuery(e.target).data('index');
            f.options[idx].label = e.target.value;
        });

        node.on('input', '.gfm-opt-value', (e) => {
            const idx = jQuery(e.target).data('index');
            f.options[idx].value = e.target.value;
        });

        node.on('click', '.gfm-opt-remove', (e) => {
            const idx = jQuery(e.currentTarget).data('index');
            f.options.splice(idx, 1);
            this.updateOptionsUI(node, f);
        });

        return node;
    }

    /**
     * Refreshes only the options sub-list for multi-choice fields.
     */
    updateOptionsUI(node, f) {
        const list = node.find('.gfm-options-list');
        list.empty();
        f.options.forEach((o, i) => {
            list.append(`
                <div class="gfm-opt-row">
                    <span class="dashicons dashicons-menu gfm-opt-drag"></span>
                    <input type="text" class="gfm-opt-label" data-index="${i}" value="${o.label}">
                    <input type="text" class="gfm-opt-value" data-index="${i}" value="${o.value}">
                    <button type="button" class="gfm-opt-btn gfm-opt-remove" data-index="${i}">
                        <span class="dashicons dashicons-no"></span>
                    </button>
                </div>
            `);
        });
    }

    /**
     * Renders the HTML for the options management section.
     */
    renderOptionsSetter(f) {
        if (!this.isOptionField(f.type)) return '';
        const listHtml = f.options.map((o, i) => `
            <div class="gfm-opt-row">
                <span class="dashicons dashicons-menu gfm-opt-drag"></span>
                <input type="text" class="gfm-opt-label" data-index="${i}" value="${o.label}">
                <input type="text" class="gfm-opt-value" data-index="${i}" value="${o.value}">
                <button type="button" class="gfm-opt-btn gfm-opt-remove" data-index="${i}">
                    <span class="dashicons dashicons-no"></span>
                </button>
            </div>
        `).join('');

        return `
            <div class="gfm-options-setter">
                <h4>Options</h4>
                <div class="gfm-options-list">${listHtml}</div>
                <button type="button" class="gfm-add-opt-btn">Add Option</button>
            </div>
        `;
    }

    /**
     * Orchestrates the collection of all current UI state into the hidden save inputs.
     */
    save() {
        const settings = {
            submit_text: jQuery('#gfm-submit-text').val(),
            con_type: jQuery('#gfm-con-type').val(),
            success_message: jQuery('#gfm-success-message').val(),
            error_message: jQuery('#gfm-error-message').val(),
            redirect_url: jQuery('#gfm-redirect-url').val(),
            admin_email: jQuery('#gfm-admin-email').val(),
            from_name: jQuery('#gfm-from-name').val(),
        };

        this.dataInput.val(JSON.stringify({ fields: this.fields }));
        this.settingsInput.val(JSON.stringify(settings));
    }
}

jQuery(document).ready(() => new GenFormBuilder());
