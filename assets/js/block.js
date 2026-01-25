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
        title: genformBlockData?.i18n?.title || 'GenForm',
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
            var i18n = genformBlockData?.i18n || {};

            function onChangeFormId(newFormId) {
                props.setAttributes({ formId: parseInt(newFormId) || 0 });
            }

            // Prepare form options
            var formOptions = [{ label: i18n.selectDefault || 'Select a form', value: 0 }];
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
                    el(PanelBody, { title: i18n.formSettings || 'Form Settings', initialOpen: true },
                        el(SelectControl, {
                            label: i18n.selectForm || 'Select Form',
                            value: formId,
                            options: formOptions,
                            onChange: onChangeFormId
                        })
                    )
                ),
                el('div', { className: props.className },
                    el('div', { className: 'gfm-block-preview' },
                        el('span', { className: 'dashicons dashicons-feedback gfm-block-icon' }),
                        el('h3', {}, i18n.title || 'GenForm'),
                        formId > 0
                            ? el('p', {}, (i18n.formIdLabel || 'Form ID: ') + formId)
                            : el('p', {}, i18n.selectError || 'Please select a form from the sidebar.')
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
