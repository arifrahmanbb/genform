<?php

/**
 * Template Renderer
 *
 * Responsible for rendering the template library modals in the admin
 * footer. Keeps all view logic separated from the Manager (data) and
 * Core (bootstrap) classes.
 *
 * @package GenForm
 * @since   1.1.0
 */

namespace GenForm\Templates;

if (! defined('ABSPATH')) {
    exit;
}

final class Renderer
{

    /**
     * Output the Template Library and Preview modals.
     *
     * Called from Core::outputGlobalModals via an include or direct
     * method call.
     */
    public static function render(): void
    {
        $categories = Manager::getCategories();
        $templates  = Manager::getAll();

        self::renderLibraryModal($categories, $templates);
        self::renderPreviewModal();
    }

    /**
     * Render the searchable, filterable template library grid modal.
     *
     * @param array<string, string>             $categories slug => label.
     * @param array<int, array<string, mixed>>  $templates  Template data.
     */
    private static function renderLibraryModal(array $categories, array $templates): void
    {
?>
        <!-- Template Library Modal -->
        <div id="gfm-templates-modal" class="gfm-modal gfm-hidden">
            <div class="gfm-modal-content gfm-modal-templates">
                <div class="gfm-modal-header">
                    <div class="gfm-templates-header-left">
                        <span class="dashicons dashicons-layout"></span>
                        <h3><?php esc_html_e('Template Library', 'genform'); ?></h3>
                        <span class="gfm-templates-count"><?php echo (int) count($templates); ?> <?php esc_html_e('Templates', 'genform'); ?></span>
                    </div>
                    <span class="gfm-close-modal dashicons dashicons-no" title="<?php esc_attr_e('Close', 'genform'); ?>"></span>
                </div>

                <div class="gfm-templates-toolbar">
                    <div class="gfm-templates-search">
                        <span class="dashicons dashicons-search"></span>
                        <input type="text" id="gfm-template-search" placeholder="<?php esc_attr_e('Search templates...', 'genform'); ?>" autocomplete="off">
                    </div>
                    <div class="gfm-templates-filters" id="gfm-template-filters">
                        <?php foreach ($categories as $cat_slug => $cat_name) : ?>
                            <button type="button"
                                class="gfm-filter-btn<?php echo 'all' === $cat_slug ? ' active' : ''; ?>"
                                data-category="<?php echo esc_attr($cat_slug); ?>">
                                <?php echo esc_html($cat_name); ?>
                            </button>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="gfm-modal-body gfm-templates-body">
                    <div class="gfm-templates-grid" id="gfm-templates-grid">
                        <?php foreach ($templates as $index => $tpl) : ?>
                            <div class="gfm-template-card"
                                data-category="<?php echo esc_attr($tpl['category']); ?>"
                                data-slug="<?php echo esc_attr($tpl['slug']); ?>"
                                data-index="<?php echo (int) $index; ?>">

                                <div class="gfm-template-preview-area">
                                    <div class="gfm-template-icon-wrap">
                                        <span class="dashicons <?php echo esc_attr($tpl['icon']); ?>"></span>
                                    </div>
                                    <div class="gfm-template-field-preview">
                                        <?php
                                        $preview_count = min(count($tpl['fields']), 4);
                                        for ($i = 0; $i < $preview_count; $i++) :
                                            $field = $tpl['fields'][$i];
                                        ?>
                                            <div class="gfm-tpl-field-row">
                                                <span class="gfm-tpl-field-label"><?php echo esc_html($field['label']); ?></span>
                                                <span class="gfm-tpl-field-bar"></span>
                                            </div>
                                        <?php endfor; ?>
                                        <?php if (count($tpl['fields']) > 4) : ?>
                                            <div class="gfm-tpl-field-more">
                                                +<?php echo (int) (count($tpl['fields']) - 4); ?> <?php esc_html_e('more fields', 'genform'); ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <div class="gfm-template-card-body">
                                    <h4 class="gfm-template-card-title"><?php echo esc_html($tpl['name']); ?></h4>
                                    <p class="gfm-template-card-desc"><?php echo esc_html($tpl['description']); ?></p>
                                    <div class="gfm-template-card-meta">
                                        <span class="gfm-tpl-meta-badge">
                                            <span class="dashicons dashicons-editor-ul"></span>
                                            <?php echo (int) count($tpl['fields']); ?> <?php esc_html_e('fields', 'genform'); ?>
                                        </span>
                                        <span class="gfm-tpl-meta-badge gfm-tpl-cat-badge">
                                            <?php echo esc_html($categories[$tpl['category']] ?? $tpl['category']); ?>
                                        </span>
                                    </div>
                                    <div class="gfm-template-card-actions">
                                        <button type="button" class="gfm-btn gfm-btn-outline gfm-btn-sm gfm-template-preview-btn" data-index="<?php echo (int) $index; ?>">
                                            <span class="dashicons dashicons-visibility"></span>
                                            <?php esc_html_e('Preview', 'genform'); ?>
                                        </button>
                                        <button type="button" class="gfm-btn gfm-btn-primary gfm-btn-sm gfm-template-use-btn" data-index="<?php echo (int) $index; ?>">
                                            <span class="dashicons dashicons-plus-alt2"></span>
                                            <?php esc_html_e('Use Template', 'genform'); ?>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <div class="gfm-templates-empty gfm-hidden" id="gfm-templates-empty">
                        <span class="dashicons dashicons-search"></span>
                        <p><?php esc_html_e('No templates match your search.', 'genform'); ?></p>
                    </div>
                </div>
            </div>
        </div>
    <?php
    }

    /**
     * Render the detailed template preview modal (populated by JS).
     */
    private static function renderPreviewModal(): void
    {
    ?>
        <!-- Template Preview Modal (Detailed View) -->
        <div id="gfm-template-preview-modal" class="gfm-modal gfm-hidden">
            <div class="gfm-modal-content gfm-modal-large">
                <div class="gfm-modal-header">
                    <div class="gfm-templates-header-left">
                        <button type="button" class="gfm-preview-back-btn" id="gfm-preview-back" title="<?php esc_attr_e('Back to Library', 'genform'); ?>">
                            <span class="dashicons dashicons-arrow-left-alt2"></span>
                        </button>
                        <h3 id="gfm-preview-title"></h3>
                    </div>
                    <span class="gfm-close-modal dashicons dashicons-no" title="<?php esc_attr_e('Close', 'genform'); ?>"></span>
                </div>
                <div class="gfm-modal-body" id="gfm-preview-body">
                    <!-- Dynamically rendered by JS -->
                </div>
                <div class="gfm-preview-footer">
                    <button type="button" class="gfm-btn gfm-btn-outline" id="gfm-preview-cancel">
                        <?php esc_html_e('Cancel', 'genform'); ?>
                    </button>
                    <button type="button" class="gfm-btn gfm-btn-primary" id="gfm-preview-use">
                        <span class="dashicons dashicons-plus-alt2"></span>
                        <?php esc_html_e('Use This Template', 'genform'); ?>
                    </button>
                </div>
            </div>
        </div>
<?php
    }
}
