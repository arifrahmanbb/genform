/**
 * GenForm Form Builder JavaScript
 * 
 * @package GenForm
 * @since 1.0.0
 */

(function ($) {
    'use strict';

    var GenFormBuilder = {
        fields: [],
        fieldCounter: 0,

        init: function () {
            this.bindEvents();
            this.loadInitialData();
            this.initSortable();
        },

        bindEvents: function () {
            var self = this;

            // Add field buttons
            $('.genform-add-field').on('click', function (e) {
                e.preventDefault();
                var fieldType = $(this).data('field-type');
                self.addField(fieldType);
            });

            // Form submission
            $('#genform-builder-form').on('submit', function (e) {
                self.saveFormData();
            });
        },

        loadInitialData: function () {
            if (typeof genformBuilder !== 'undefined' && genformBuilder.initialData && genformBuilder.initialData.fields) {
                this.fields = genformBuilder.initialData.fields;
                this.renderAllFields();
            }
        },

        initSortable: function () {
            var self = this;
            $('#genform-fields-container').sortable({
                handle: '.genform-field-item-header',
                placeholder: 'ui-sortable-placeholder',
                update: function (event, ui) {
                    self.updateFieldOrder();
                }
            });
        },

        addField: function (type) {
            var field = {
                id: 'field_' + (++this.fieldCounter),
                type: type,
                label: this.getDefaultLabel(type),
                name: 'field_' + this.fieldCounter,
                placeholder: '',
                required: false,
                class: '',
                description: '',
                options: (type === 'select' || type === 'radio' || type === 'checkbox') ? [
                    { label: 'Option 1', value: 'option_1' },
                    { label: 'Option 2', value: 'option_2' }
                ] : []
            };

            this.fields.push(field);
            this.renderField(field);
        },

        getDefaultLabel: function (type) {
            var labels = {
                'text': 'Text Field',
                'email': 'Email Address',
                'textarea': 'Message',
                'number': 'Number',
                'tel': 'Phone Number',
                'url': 'Website URL',
                'select': 'Dropdown',
                'radio': 'Radio Buttons',
                'checkbox': 'Checkboxes'
            };
            return labels[type] || 'Field';
        },

        renderAllFields: function () {
            var self = this;
            $('#genform-fields-container').empty();
            $.each(this.fields, function (index, field) {
                self.renderField(field);
            });
        },

        renderField: function (field) {
            var $container = $('#genform-fields-container');
            var $fieldItem = $('<div class="genform-field-item" data-field-id="' + field.id + '"></div>');

            // Header
            var $header = $('<div class="genform-field-item-header"></div>');
            $header.append('<span class="genform-field-item-title">' + field.label + ' (' + field.type + ')</span>');

            var $actions = $('<div class="genform-field-item-actions"></div>');
            $actions.append('<button type="button" class="button button-small genform-edit-field">Edit</button>');
            $actions.append('<button type="button" class="button button-small genform-delete-field">Delete</button>');
            $header.append($actions);

            $fieldItem.append($header);

            // Preview
            var $preview = $('<div class="genform-field-preview"></div>');
            $preview.html(this.getFieldPreviewHTML(field));
            $fieldItem.append($preview);

            // Settings
            var $settings = $('<div class="genform-field-settings"></div>');
            $settings.html(this.getFieldSettingsHTML(field));
            $fieldItem.append($settings);

            $container.append($fieldItem);

            this.bindFieldEvents($fieldItem, field);
        },

        getFieldPreviewHTML: function (field) {
            var html = '';
            var required = field.required ? '<span class="genform-required">*</span>' : '';

            if (field.label) {
                html += '<label>' + field.label + required + '</label>';
            }

            switch (field.type) {
                case 'text':
                case 'email':
                case 'tel':
                case 'number':
                case 'url':
                    html += '<input type="' + field.type + '" placeholder="' + field.placeholder + '" />';
                    break;

                case 'textarea':
                    html += '<textarea placeholder="' + field.placeholder + '" rows="4"></textarea>';
                    break;

                case 'select':
                    html += '<select><option>Select an option</option>';
                    $.each(field.options || [], function (i, opt) {
                        html += '<option>' + opt.label + '</option>';
                    });
                    html += '</select>';
                    break;

                case 'radio':
                    $.each(field.options || [], function (i, opt) {
                        html += '<label><input type="radio" name="preview_' + field.id + '" /> ' + opt.label + '</label><br/>';
                    });
                    break;

                case 'checkbox':
                    $.each(field.options || [], function (i, opt) {
                        html += '<label><input type="checkbox" /> ' + opt.label + '</label><br/>';
                    });
                    break;
            }

            if (field.description) {
                html += '<p class="genform-field-description">' + field.description + '</p>';
            }

            return html;
        },

        getFieldSettingsHTML: function (field) {
            var html = '';

            // Label
            html += '<div class="genform-field-setting">';
            html += '<label>Label</label>';
            html += '<input type="text" class="field-label" value="' + (field.label || '') + '" />';
            html += '</div>';

            // Name
            html += '<div class="genform-field-setting">';
            html += '<label>Field Name</label>';
            html += '<input type="text" class="field-name" value="' + (field.name || '') + '" />';
            html += '</div>';

            // Placeholder (not for radio/checkbox/select)
            if (!['radio', 'checkbox', 'select'].includes(field.type)) {
                html += '<div class="genform-field-setting">';
                html += '<label>Placeholder</label>';
                html += '<input type="text" class="field-placeholder" value="' + (field.placeholder || '') + '" />';
                html += '</div>';
            }

            // Options (for select, radio, checkbox)
            if (['select', 'radio', 'checkbox'].includes(field.type)) {
                html += '<div class="genform-field-setting">';
                html += '<label>Options</label>';
                html += '<div class="genform-options-list">';

                $.each(field.options || [], function (i, opt) {
                    html += '<div class="genform-option-item">';
                    html += '<input type="text" class="option-label" value="' + opt.label + '" placeholder="Label" />';
                    html += '<input type="text" class="option-value" value="' + opt.value + '" placeholder="Value" />';
                    html += '<button type="button" class="button button-small genform-remove-option">Remove</button>';
                    html += '</div>';
                });

                html += '</div>';
                html += '<button type="button" class="button button-small genform-add-option">Add Option</button>';
                html += '</div>';
            }

            // Description
            html += '<div class="genform-field-setting">';
            html += '<label>Description</label>';
            html += '<input type="text" class="field-description" value="' + (field.description || '') + '" />';
            html += '</div>';

            // Required
            html += '<div class="genform-field-setting">';
            html += '<label><input type="checkbox" class="field-required" ' + (field.required ? 'checked' : '') + ' /> Required Field</label>';
            html += '</div>';

            // CSS Class
            html += '<div class="genform-field-setting">';
            html += '<label>CSS Class</label>';
            html += '<input type="text" class="field-class" value="' + (field.class || '') + '" />';
            html += '</div>';

            return html;
        },

        bindFieldEvents: function ($fieldItem, field) {
            var self = this;

            // Edit button
            $fieldItem.find('.genform-edit-field').on('click', function () {
                $fieldItem.find('.genform-field-settings').toggleClass('active');
            });

            // Delete button
            $fieldItem.find('.genform-delete-field').on('click', function () {
                if (confirm('Are you sure you want to delete this field?')) {
                    self.deleteField(field.id);
                    $fieldItem.remove();
                }
            });

            // Field settings changes
            $fieldItem.find('.field-label').on('input', function () {
                field.label = $(this).val();
                $fieldItem.find('.genform-field-item-title').text(field.label + ' (' + field.type + ')');
                $fieldItem.find('.genform-field-preview').html(self.getFieldPreviewHTML(field));
            });

            $fieldItem.find('.field-name').on('input', function () {
                field.name = $(this).val();
            });

            $fieldItem.find('.field-placeholder').on('input', function () {
                field.placeholder = $(this).val();
                $fieldItem.find('.genform-field-preview').html(self.getFieldPreviewHTML(field));
            });

            $fieldItem.find('.field-description').on('input', function () {
                field.description = $(this).val();
                $fieldItem.find('.genform-field-preview').html(self.getFieldPreviewHTML(field));
            });

            $fieldItem.find('.field-required').on('change', function () {
                field.required = $(this).is(':checked');
                $fieldItem.find('.genform-field-preview').html(self.getFieldPreviewHTML(field));
            });

            $fieldItem.find('.field-class').on('input', function () {
                field.class = $(this).val();
            });

            // Options management
            $fieldItem.find('.genform-add-option').on('click', function () {
                if (!field.options) field.options = [];
                var optionNum = field.options.length + 1;
                field.options.push({
                    label: 'Option ' + optionNum,
                    value: 'option_' + optionNum
                });
                $fieldItem.find('.genform-field-settings').html(self.getFieldSettingsHTML(field));
                $fieldItem.find('.genform-field-preview').html(self.getFieldPreviewHTML(field));
                self.bindFieldEvents($fieldItem, field);
            });

            $fieldItem.find('.genform-remove-option').on('click', function () {
                var $optionItem = $(this).closest('.genform-option-item');
                var index = $optionItem.index();
                field.options.splice(index, 1);
                $fieldItem.find('.genform-field-settings').html(self.getFieldSettingsHTML(field));
                $fieldItem.find('.genform-field-preview').html(self.getFieldPreviewHTML(field));
                self.bindFieldEvents($fieldItem, field);
            });

            $fieldItem.find('.option-label').on('input', function () {
                var $optionItem = $(this).closest('.genform-option-item');
                var index = $optionItem.index();
                field.options[index].label = $(this).val();
                $fieldItem.find('.genform-field-preview').html(self.getFieldPreviewHTML(field));
            });

            $fieldItem.find('.option-value').on('input', function () {
                var $optionItem = $(this).closest('.genform-option-item');
                var index = $optionItem.index();
                field.options[index].value = $(this).val();
            });
        },

        deleteField: function (fieldId) {
            this.fields = this.fields.filter(function (field) {
                return field.id !== fieldId;
            });
        },

        updateFieldOrder: function () {
            var self = this;
            var newOrder = [];

            $('#genform-fields-container .genform-field-item').each(function () {
                var fieldId = $(this).data('field-id');
                var field = self.fields.find(function (f) {
                    return f.id === fieldId;
                });
                if (field) {
                    newOrder.push(field);
                }
            });

            this.fields = newOrder;
        },

        saveFormData: function () {
            var formData = {
                fields: this.fields
            };

            var formSettings = {
                submit_text: $('#genform-submit-text').val() || 'Submit',
                success_message: $('#genform-success-message').val() || 'Thank you! Your form has been submitted successfully.',
                redirect_url: $('#genform-redirect-url').val() || '',
                disable_admin_notification: $('#genform-disable-admin-notification').is(':checked'),
                admin_email: $('#genform-admin-email').val() || '',
                enable_user_confirmation: $('#genform-enable-user-confirmation').is(':checked'),
                user_email_subject: $('#genform-user-email-subject').val() || '',
                user_email_message: $('#genform-user-email-message').val() || ''
            };

            $('#genform-data-input').val(JSON.stringify(formData));
            $('#genform-settings-input').val(JSON.stringify(formSettings));
        }
    };

    // Initialize on document ready
    $(document).ready(function () {
        if ($('#genform-builder-form').length) {
            GenFormBuilder.init();
        }
    });

})(jQuery);
