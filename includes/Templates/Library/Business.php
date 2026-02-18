<?php

/**
 * Business Form Templates
 *
 * Professional and corporate form templates including support tickets,
 * job applications, quote requests, and bug reports.
 *
 * @package GenForm
 * @since   1.1.0
 */

namespace GenForm\Templates\Library;

if (! defined('ABSPATH')) {
    exit;
}

final class Business
{

    /**
     * Category slug used for grouping.
     */
    public const SLUG = 'business';

    /**
     * Return all templates in this category.
     *
     * @return array<int, array<string, mixed>>
     */
    public static function get(): array
    {
        return [
            [
                'slug'        => 'support-ticket',
                'name'        => esc_html__('Support Ticket', 'genform'),
                'description' => esc_html__('Submit support requests with priority, department, and description fields for help desks.', 'genform'),
                'icon'        => 'dashicons-sos',
                'category'    => self::SLUG,
                'fields'      => [
                    ['type' => 'text',     'label' => 'Full Name',    'required' => true,  'placeholder' => 'Your name'],
                    ['type' => 'email',    'label' => 'Email',        'required' => true,  'placeholder' => 'you@example.com'],
                    ['type' => 'select',   'label' => 'Department',   'required' => true,  'placeholder' => '', 'options' => ['Sales', 'Technical Support', 'Billing', 'General Inquiry']],
                    ['type' => 'select',   'label' => 'Priority',     'required' => true,  'placeholder' => '', 'options' => ['Low', 'Medium', 'High', 'Urgent']],
                    ['type' => 'text',     'label' => 'Subject',      'required' => true,  'placeholder' => 'Brief summary of your issue'],
                    ['type' => 'textarea', 'label' => 'Description',  'required' => true,  'placeholder' => 'Please describe your issue in detail...'],
                ],
                'settings'    => [
                    'con_type'        => 'message',
                    'success_message' => 'Ticket submitted! Our team will respond within 24 hours.',
                    'submit_text'     => 'Submit Ticket',
                    'submit_align'    => 'full',
                ],
            ],
            [
                'slug'        => 'job-application',
                'name'        => esc_html__('Job Application', 'genform'),
                'description' => esc_html__('Employment application form with personal details, position, experience, and availability.', 'genform'),
                'icon'        => 'dashicons-businessperson',
                'category'    => self::SLUG,
                'fields'      => [
                    ['type' => 'text',     'label' => 'Full Name',     'required' => true,  'placeholder' => 'First and last name'],
                    ['type' => 'email',    'label' => 'Email',         'required' => true,  'placeholder' => 'candidate@email.com'],
                    ['type' => 'tel',      'label' => 'Phone',         'required' => true,  'placeholder' => 'Phone number'],
                    ['type' => 'text',     'label' => 'Position Applied For', 'required' => true, 'placeholder' => 'e.g. Marketing Manager'],
                    ['type' => 'url',      'label' => 'LinkedIn / Portfolio', 'required' => false, 'placeholder' => 'https://'],
                    ['type' => 'select',   'label' => 'Experience Level', 'required' => true, 'placeholder' => '', 'options' => ['Entry Level', '1-3 Years', '3-5 Years', '5-10 Years', '10+ Years']],
                    ['type' => 'select',   'label' => 'Start Availability', 'required' => true, 'placeholder' => '', 'options' => ['Immediately', '2 Weeks', '1 Month', 'Flexible']],
                    ['type' => 'textarea', 'label' => 'Cover Letter',  'required' => false, 'placeholder' => 'Tell us why you\'re a great fit...'],
                ],
                'settings'    => [
                    'con_type'        => 'message',
                    'success_message' => 'Application received! We\'ll review and get back to you soon.',
                    'submit_text'     => 'Apply Now',
                    'submit_align'    => 'full',
                ],
            ],
            [
                'slug'        => 'request-quote',
                'name'        => esc_html__('Request a Quote', 'genform'),
                'description' => esc_html__('Quote request form for service-based businesses with budget range and project details.', 'genform'),
                'icon'        => 'dashicons-money-alt',
                'category'    => self::SLUG,
                'fields'      => [
                    ['type' => 'text',     'label' => 'Company Name', 'required' => true,  'placeholder' => 'Your company'],
                    ['type' => 'text',     'label' => 'Contact Name', 'required' => true,  'placeholder' => 'Your full name'],
                    ['type' => 'email',    'label' => 'Email',        'required' => true,  'placeholder' => 'business@email.com'],
                    ['type' => 'tel',      'label' => 'Phone',        'required' => false, 'placeholder' => 'Phone number'],
                    ['type' => 'select',   'label' => 'Service Needed', 'required' => true, 'placeholder' => '', 'options' => ['Web Design', 'Web Development', 'SEO / Marketing', 'Branding', 'Consulting', 'Other']],
                    ['type' => 'select',   'label' => 'Budget Range', 'required' => true, 'placeholder' => '', 'options' => ['Under $1,000', '$1,000 - $5,000', '$5,000 - $10,000', '$10,000 - $25,000', '$25,000+']],
                    ['type' => 'textarea', 'label' => 'Project Details', 'required' => true, 'placeholder' => 'Describe your project requirements...'],
                ],
                'settings'    => [
                    'con_type'        => 'message',
                    'success_message' => 'Quote request received! We\'ll send you a detailed proposal.',
                    'submit_text'     => 'Get a Quote',
                    'submit_align'    => 'full',
                ],
            ],
            [
                'slug'        => 'bug-report',
                'name'        => esc_html__('Bug Report', 'genform'),
                'description' => esc_html__('Software bug report form with severity, steps to reproduce, and environment details.', 'genform'),
                'icon'        => 'dashicons-warning',
                'category'    => self::SLUG,
                'fields'      => [
                    ['type' => 'text',     'label' => 'Your Name',          'required' => true,  'placeholder' => 'Reporter name'],
                    ['type' => 'email',    'label' => 'Email',              'required' => true,  'placeholder' => 'reporter@email.com'],
                    ['type' => 'text',     'label' => 'Bug Title',          'required' => true,  'placeholder' => 'Short summary of the bug'],
                    ['type' => 'select',   'label' => 'Severity',           'required' => true,  'placeholder' => '', 'options' => ['Critical', 'Major', 'Minor', 'Cosmetic']],
                    ['type' => 'text',     'label' => 'Browser / OS',       'required' => false, 'placeholder' => 'e.g. Chrome 120 on Windows 11'],
                    ['type' => 'textarea', 'label' => 'Steps to Reproduce', 'required' => true,  'placeholder' => "1. Go to...\n2. Click on...\n3. Observe..."],
                    ['type' => 'textarea', 'label' => 'Expected vs Actual', 'required' => true,  'placeholder' => 'What should happen vs. what actually happens...'],
                ],
                'settings'    => [
                    'con_type'        => 'message',
                    'success_message' => 'Bug report submitted. Our development team will investigate.',
                    'submit_text'     => 'Report Bug',
                    'submit_align'    => 'full',
                ],
            ],
        ];
    }
}
