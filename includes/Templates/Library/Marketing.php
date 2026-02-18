<?php

/**
 * Marketing Form Templates
 *
 * Lead generation, newsletter, and event registration form templates
 * designed for marketing and sales funnels.
 *
 * @package GenForm
 * @since   1.1.0
 */

namespace GenForm\Templates\Library;

if (! defined('ABSPATH')) {
    exit;
}

final class Marketing
{

    /**
     * Category slug used for grouping.
     */
    public const SLUG = 'marketing';

    /**
     * Return all templates in this category.
     *
     * @return array<int, array<string, mixed>>
     */
    public static function get(): array
    {
        return [
            [
                'slug'        => 'newsletter-signup',
                'name'        => esc_html__('Newsletter Signup', 'genform'),
                'description' => esc_html__('Quick email subscription form to grow your mailing list. Minimal and effective.', 'genform'),
                'icon'        => 'dashicons-megaphone',
                'category'    => self::SLUG,
                'fields'      => [
                    ['type' => 'text',  'label' => 'First Name', 'required' => true,  'placeholder' => 'Jane'],
                    ['type' => 'email', 'label' => 'Email',      'required' => true,  'placeholder' => 'jane@example.com'],
                ],
                'settings'    => [
                    'con_type'        => 'message',
                    'success_message' => 'You\'re subscribed! Check your inbox for a welcome email.',
                    'submit_text'     => 'Subscribe',
                    'submit_align'    => 'full',
                ],
            ],
            [
                'slug'        => 'lead-generation',
                'name'        => esc_html__('Lead Generation', 'genform'),
                'description' => esc_html__('Capture leads with company, role, and interest fields for sales and marketing funnels.', 'genform'),
                'icon'        => 'dashicons-chart-line',
                'category'    => self::SLUG,
                'fields'      => [
                    ['type' => 'text',   'label' => 'Full Name',     'required' => true,  'placeholder' => 'Your name'],
                    ['type' => 'email',  'label' => 'Work Email',    'required' => true,  'placeholder' => 'you@company.com'],
                    ['type' => 'text',   'label' => 'Company',       'required' => true,  'placeholder' => 'Company name'],
                    ['type' => 'text',   'label' => 'Job Title',     'required' => false, 'placeholder' => 'Your role'],
                    ['type' => 'select', 'label' => 'Company Size',  'required' => true,  'placeholder' => '', 'options' => ['1-10', '11-50', '51-200', '201-500', '500+']],
                    ['type' => 'select', 'label' => 'Interest',      'required' => true,  'placeholder' => '', 'options' => ['Product Demo', 'Pricing Info', 'Partnership', 'General Inquiry']],
                ],
                'settings'    => [
                    'con_type'        => 'message',
                    'success_message' => 'Thanks! Our sales team will be in touch within 24 hours.',
                    'submit_text'     => 'Get Started',
                    'submit_align'    => 'full',
                ],
            ],
            [
                'slug'        => 'event-registration',
                'name'        => esc_html__('Event Registration', 'genform'),
                'description' => esc_html__('Full event registration with attendee details, ticket type, and payment method.', 'genform'),
                'icon'        => 'dashicons-calendar',
                'category'    => self::SLUG,
                'fields'      => [
                    ['type' => 'text',     'label' => 'Full Name',     'required' => true,  'placeholder' => 'Attendee name'],
                    ['type' => 'email',    'label' => 'Email',         'required' => true,  'placeholder' => 'your@email.com'],
                    ['type' => 'tel',      'label' => 'Phone',         'required' => false, 'placeholder' => 'Phone number'],
                    ['type' => 'text',     'label' => 'Organization',  'required' => false, 'placeholder' => 'Company or organization'],
                    ['type' => 'select',   'label' => 'Ticket Type',   'required' => true,  'placeholder' => '', 'options' => ['General Admission', 'VIP', 'Student', 'Early Bird']],
                    ['type' => 'checkbox', 'label' => 'Sessions of Interest', 'required' => false, 'options' => ['Opening Keynote', 'Workshop A', 'Workshop B', 'Panel Discussion', 'Networking Mixer']],
                    ['type' => 'textarea', 'label' => 'Accessibility Needs', 'required' => false, 'placeholder' => 'Let us know if you have any special requirements...'],
                ],
                'settings'    => [
                    'con_type'        => 'message',
                    'success_message' => 'You\'re registered! Check your email for the event ticket.',
                    'submit_text'     => 'Register',
                    'submit_align'    => 'full',
                ],
            ],
        ];
    }
}
