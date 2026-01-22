class GenFormBuilder {
    constructor() {
        this.fields = [];
        this.counter = 0;
        this.container = jQuery('#gfm-fields-container');
        this.init();
    }

    init() {
        this.bindEvents();
        this.loadData();
    }

    bindEvents() {
        jQuery('.gfm-add-field').on('click', (e) => {
            const type = jQuery(e.currentTarget).data('type');
            this.addField(type);
        });

        jQuery('#gfm-builder-form').on('submit', () => this.prepareSave());
    }

    addField(type) {
        const field = {
            id: ++this.counter,
            type: type,
            label: `New ${type} Field`,
            name: `field_${this.counter}`,
            placeholder: '',
            required: false,
            options: type === 'select' ? [{ label: 'Option 1', value: '1' }] : []
        };
        this.fields.push(field);
        this.render();
    }

    render() {
        this.container.empty();
        this.fields.forEach(field => {
            const node = jQuery(`
                <div class="gfm-form-field gfm-card" data-id="${field.id}">
                    <div class="gfm-field-header">
                        <strong>${field.label} (${field.type})</strong>
                        <button type="button" class="gfm-remove-js">×</button>
                    </div>
                    <div class="gfm-field-props">
                        <input type="text" class="gfm-label-js" value="${field.label}" placeholder="Label">
                        <input type="text" class="gfm-name-js" value="${field.name}" placeholder="Internal Name">
                    </div>
                </div>
            `);

            node.find('.gfm-label-js').on('input', (e) => {
                field.label = e.target.value;
                node.find('strong').text(`${field.label} (${field.type})`);
            });

            node.find('.gfm-remove-js').on('click', () => {
                this.fields = this.fields.filter(f => f.id !== field.id);
                node.remove();
            });

            this.container.append(node);
        });
    }

    prepareSave() {
        const settings = {
            submit_text: jQuery('#gfm-submit-text').val(),
            success_message: jQuery('#gfm-success-message').val()
        };
        jQuery('#gfm-data-input').val(JSON.stringify({ fields: this.fields }));
        jQuery('#gfm-settings-input').val(JSON.stringify(settings));
    }

    loadData() {
        // Implementation for loading initial data if editing
        const initial = window.genformBuilder?.initialData?.fields || [];
        if (initial.length) {
            this.fields = initial;
            this.counter = Math.max(0, ...this.fields.map(f => f.id));
            this.render();
        }
    }
}

jQuery(document).ready(() => new GenFormBuilder());
