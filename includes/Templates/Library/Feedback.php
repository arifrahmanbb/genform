<?php

/**
 * Feedback Form Templates
 *
 * Customer satisfaction and feedback-collection form templates.
 *
 * @package GenForm
 * @since   1.1.0
 */

namespace GenForm\Templates\Library;

if (! defined('ABSPATH')) {
    exit;
}

final class Feedback
{

    /**
     * Category slug used for grouping.
     */
    public const SLUG = 'feedback';

    /**
     * Return all templates in this category.
     *
     * @return array<int, array<string, mixed>>
     */
    public static function get(): array
    {
        return [
            [
                'slug'        => 'customer-feedback',
                'name'        => esc_html__('Customer Feedback', 'genform'),
                'description' => esc_html__('Gather customer feedback with satisfaction rating, comments, and improvement suggestions.', 'genform'),
                'icon'        => 'dashicons-star-filled',
                'category'    => self::SLUG,
                'fields'      => [
                    ['type' => 'text',     'label' => 'Full Name',          'required' => false, 'placeholder' => 'Optional — your name'],
                    ['type' => 'email',    'label' => 'Email',              'required' => false, 'placeholder' => 'Optional — for follow-up'],
                    ['type' => 'select',   'label' => 'Overall Experience', 'required' => true,  'placeholder' => '', 'options' => ['Excellent', 'Good', 'Average', 'Below Average', 'Poor']],
                    ['type' => 'radio',    'label' => 'Would you recommend us?', 'required' => true, 'options' => ['Yes, definitely', 'Maybe', 'No']],
                    ['type' => 'textarea', 'label' => 'What did you enjoy most?', 'required' => false, 'placeholder' => 'Tell us the highlights...'],
                    ['type' => 'textarea', 'label' => 'How can we improve?',      'required' => false, 'placeholder' => 'We value your suggestions...'],
                ],
                'settings'    => [
                    'con_type'        => 'message',
                    'success_message' => 'Thank you for your feedback! It helps us improve.',
                    'submit_text'     => 'Submit Feedback',
                    'submit_align'    => 'full',
                ],
            ],
        ];
    }
}
