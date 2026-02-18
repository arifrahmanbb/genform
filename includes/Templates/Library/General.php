<?php

/**
 * General Form Templates
 *
 * Common, multi-purpose form templates like contact forms,
 * event RSVPs, and volunteer signups.
 *
 * @package GenForm
 * @since   1.1.0
 */

namespace GenForm\Templates\Library;

if (! defined('ABSPATH')) {
    exit;
}

final class General
{

    /**
     * Category slug used for grouping.
     */
    public const SLUG = 'general';

    /**
     * Return all templates in this category.
     *
     * @return array<int, array<string, mixed>>
     */
    public static function get(): array
    {
        return [
            [
                'slug'        => 'simple-contact',
                'name'        => esc_html__('Simple Contact Form', 'genform'),
                'description' => esc_html__('A clean, minimal contact form with name, email, and message fields. Perfect for any website.', 'genform'),
                'icon'        => 'dashicons-email-alt',
                'category'    => self::SLUG,
                'fields'      => [
                    ['type' => 'text',     'label' => 'Full Name',  'required' => true,  'placeholder' => 'John Doe'],
                    ['type' => 'email',    'label' => 'Email',      'required' => true,  'placeholder' => 'john@example.com'],
                    ['type' => 'text',     'label' => 'Subject',    'required' => false, 'placeholder' => 'How can we help?'],
                    ['type' => 'textarea', 'label' => 'Message',    'required' => true,  'placeholder' => 'Tell us more...'],
                ],
                'settings'    => [
                    'con_type'        => 'message',
                    'success_message' => 'Thank you for reaching out! We\'ll get back to you shortly.',
                    'submit_text'     => 'Send Message',
                    'submit_align'    => 'full',
                ],
            ],
            [
                'slug'        => 'event-rsvp',
                'name'        => esc_html__('Event RSVP', 'genform'),
                'description' => esc_html__('Event registration and RSVP form with attendance confirmation and dietary needs.', 'genform'),
                'icon'        => 'dashicons-tickets-alt',
                'category'    => self::SLUG,
                'fields'      => [
                    ['type' => 'text',     'label' => 'Full Name',        'required' => true,  'placeholder' => 'Your name'],
                    ['type' => 'email',    'label' => 'Email',            'required' => true,  'placeholder' => 'you@email.com'],
                    ['type' => 'tel',      'label' => 'Phone',            'required' => false, 'placeholder' => 'Contact number'],
                    ['type' => 'radio',    'label' => 'Will you attend?', 'required' => true,  'options' => ['Yes, I\'ll be there', 'No, can\'t make it', 'Maybe']],
                    ['type' => 'number',   'label' => 'Number of Guests', 'required' => false, 'placeholder' => '1'],
                    ['type' => 'checkbox', 'label' => 'Dietary Requirements', 'required' => false, 'options' => ['Vegetarian', 'Vegan', 'Gluten-Free', 'Halal', 'Kosher', 'None']],
                    ['type' => 'textarea', 'label' => 'Additional Notes', 'required' => false, 'placeholder' => 'Any other request?'],
                ],
                'settings'    => [
                    'con_type'        => 'message',
                    'success_message' => 'RSVP received! We\'ll send you the event details.',
                    'submit_text'     => 'Confirm RSVP',
                    'submit_align'    => 'full',
                ],
            ],
            [
                'slug'        => 'volunteer-signup',
                'name'        => esc_html__('Volunteer Signup', 'genform'),
                'description' => esc_html__('Volunteer registration form with availability, skills, and areas of interest.', 'genform'),
                'icon'        => 'dashicons-groups',
                'category'    => self::SLUG,
                'fields'      => [
                    ['type' => 'text',     'label' => 'Full Name',     'required' => true,  'placeholder' => 'Your name'],
                    ['type' => 'email',    'label' => 'Email',         'required' => true,  'placeholder' => 'you@email.com'],
                    ['type' => 'tel',      'label' => 'Phone',         'required' => true,  'placeholder' => 'Phone number'],
                    ['type' => 'checkbox', 'label' => 'Availability',  'required' => true,  'options' => ['Weekday Mornings', 'Weekday Evenings', 'Weekends', 'Flexible']],
                    ['type' => 'checkbox', 'label' => 'Areas of Interest', 'required' => false, 'options' => ['Event Setup', 'Teaching', 'Admin Support', 'Fundraising', 'Outreach']],
                    ['type' => 'textarea', 'label' => 'Skills / Experience', 'required' => false, 'placeholder' => 'Any relevant skills or past volunteer experience...'],
                ],
                'settings'    => [
                    'con_type'        => 'message',
                    'success_message' => 'Thank you for volunteering! We\'ll reach out with next steps.',
                    'submit_text'     => 'Sign Up',
                    'submit_align'    => 'full',
                ],
            ],
        ];
    }
}
