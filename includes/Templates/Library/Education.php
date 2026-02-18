<?php

/**
 * Education Form Templates
 *
 * Student enrollment and course registration form templates.
 *
 * @package GenForm
 * @since   1.1.0
 */

namespace GenForm\Templates\Library;

if (! defined('ABSPATH')) {
    exit;
}

final class Education
{

    /**
     * Category slug used for grouping.
     */
    public const SLUG = 'education';

    /**
     * Return all templates in this category.
     *
     * @return array<int, array<string, mixed>>
     */
    public static function get(): array
    {
        return [
            [
                'slug'        => 'course-enrollment',
                'name'        => esc_html__('Course Enrollment', 'genform'),
                'description' => esc_html__('Student enrollment form with personal info, course selection, and prerequisites.', 'genform'),
                'icon'        => 'dashicons-welcome-learn-more',
                'category'    => self::SLUG,
                'fields'      => [
                    ['type' => 'text',     'label' => 'Full Name',         'required' => true,  'placeholder' => 'Student name'],
                    ['type' => 'email',    'label' => 'Email',             'required' => true,  'placeholder' => 'student@email.com'],
                    ['type' => 'tel',      'label' => 'Phone',             'required' => true,  'placeholder' => 'Contact number'],
                    ['type' => 'date',     'label' => 'Date of Birth',     'required' => true,  'placeholder' => ''],
                    ['type' => 'select',   'label' => 'Course',            'required' => true,  'placeholder' => '', 'options' => ['Web Development', 'Graphic Design', 'Digital Marketing', 'Data Science', 'Business Management']],
                    ['type' => 'select',   'label' => 'Education Level',   'required' => true,  'placeholder' => '', 'options' => ['High School', 'Associate', 'Bachelor\'s', 'Master\'s', 'PhD']],
                    ['type' => 'radio',    'label' => 'Schedule Preference', 'required' => true, 'options' => ['Full-Time', 'Part-Time', 'Online / Self-Paced']],
                    ['type' => 'textarea', 'label' => 'Why are you interested?', 'required' => false, 'placeholder' => 'Tell us about your goals...'],
                ],
                'settings'    => [
                    'con_type'        => 'message',
                    'success_message' => 'Enrollment submitted! Check your email for next steps.',
                    'submit_text'     => 'Enroll Now',
                    'submit_align'    => 'full',
                ],
            ],
        ];
    }
}
