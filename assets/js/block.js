/**
 * GenForm Gutenberg Block
 * 
 * @package GenForm
 * @since 1.0.0
 */

(function (blocks, element, components, editor) {
    var el = element.createElement;
    var SelectControl = components.SelectControl;
    var InspectorControls = editor.InspectorControls;
    var PanelBody = components.PanelBody;

    blocks.registerBlockType('genform/form-block', {
        title: 'GenForm',
        icon: 'feedback',
        category: 'widgets',
        attributes: {
            formId: {
                type: 'number',
                default: 0
            }
        },

        edit: function (props) {
            var formId = props.attributes.formId;

            function onChangeFormId(newFormId) {
                props.setAttributes({ formId: parseInt(newFormId) });
            }

            // Prepare form options
            var formOptions = [{ label: 'Select a form', value: 0 }];
            if (genformBlockData && genformBlockData.forms) {
                genformBlockData.forms.forEach(function (form) {
                    formOptions.push({
                        label: form.label,
                        value: form.value
                    });
                });
            }

            return [
                el(InspectorControls, {},
                    el(PanelBody, { title: 'Form Settings', initialOpen: true },
                        el(SelectControl, {
                            label: 'Select Form',
                            value: formId,
                            options: formOptions,
                            onChange: onChangeFormId
                        })
                    )
                ),
                el('div', { className: props.className },
                    el('div', {
                        style: {
                            padding: '20px',
                            background: '#f5f5f5',
                            border: '1px solid #ddd',
                            borderRadius: '4px',
                            textAlign: 'center'
                        }
                    },
                        el('span', {
                            className: 'dashicons dashicons-feedback',
                            style: { fontSize: '48px', color: '#0073aa' }
                        }),
                        el('h3', {}, 'GenForm'),
                        formId > 0
                            ? el('p', {}, 'Form ID: ' + formId)
                            : el('p', {}, 'Please select a form from the sidebar.')
                    )
                )
            ];
        },

        save: function () {
            return null; // Rendered via PHP
        }
    });

})(
    window.wp.blocks,
    window.wp.element,
    window.wp.components,
    window.wp.blockEditor || window.wp.editor
);
