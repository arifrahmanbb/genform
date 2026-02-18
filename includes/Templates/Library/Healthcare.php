<?php

/**
 * Healthcare Form Templates
 *
 * Medical intake and patient registration form templates.
 *
 * @package GenForm
 * @since   1.1.0
 */

namespace GenForm\Templates\Library;

if (! defined('ABSPATH')) {
    exit;
}

final class Healthcare
{

    /**
     * Category slug used for grouping.
     */
    public const SLUG = 'healthcare';

    /**
     * Return all templates in this category.
     *
     * @return array<int, array<string, mixed>>
     */
    public static function get(): array
    {
        return [
            [
                'slug'        => 'patient-intake',
                'name'        => esc_html__('Patient Intake Form', 'genform'),
                'description' => esc_html__('Medical intake form with patient info, insurance, allergies, and health history.', 'genform'),
                'icon'        => 'dashicons-heart',
                'category'    => self::SLUG,
                'fields'      => [
                    ['type' => 'text',     'label' => 'Full Name',         'required' => true,  'placeholder' => 'Patient name'],
                    ['type' => 'email',    'label' => 'Email',             'required' => true,  'placeholder' => 'patient@email.com'],
                    ['type' => 'tel',      'label' => 'Phone',             'required' => true,  'placeholder' => 'Phone number'],
                    ['type' => 'date',     'label' => 'Date of Birth',     'required' => true,  'placeholder' => ''],
                    ['type' => 'select',   'label' => 'Gender',            'required' => true,  'placeholder' => '', 'options' => ['Male', 'Female', 'Non-Binary', 'Prefer not to say']],
                    ['type' => 'text',     'label' => 'Insurance Provider', 'required' => false, 'placeholder' => 'e.g. Blue Cross Blue Shield'],
                    ['type' => 'text',     'label' => 'Policy Number',     'required' => false, 'placeholder' => 'Insurance policy number'],
                    ['type' => 'checkbox', 'label' => 'Known Allergies',   'required' => false, 'options' => ['Penicillin', 'Aspirin', 'Latex', 'Shellfish', 'None']],
                    ['type' => 'textarea', 'label' => 'Current Medications', 'required' => false, 'placeholder' => 'List any medications you\'re taking...'],
                    ['type' => 'textarea', 'label' => 'Reason for Visit', 'required' => true,  'placeholder' => 'Describe your symptoms or reason...'],
                ],
                'settings'    => [
                    'con_type'        => 'message',
                    'success_message' => 'Intake form submitted. Please bring your ID to your appointment.',
                    'submit_text'     => 'Submit',
                    'submit_align'    => 'full',
                    'gdpr_enabled'    => '1',
                    'gdpr_text'       => 'I consent to my data being processed for healthcare purposes.',
                ],
            ],
        ];
    }
}
