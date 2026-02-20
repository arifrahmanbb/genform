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
		return array(
			array(
				'slug'        => 'newsletter-signup',
				'name'        => esc_html__('Newsletter Signup', 'genform'),
				'description' => esc_html__('Quick email subscription form to grow your mailing list. Minimal and effective.', 'genform'),
				'icon'        => 'dashicons-megaphone',
				'category'    => self::SLUG,
				'fields'      => array(
					array(
						'type'        => 'text',
						'label'       => 'First Name',
						'required'    => true,
						'placeholder' => 'Jane',
					),
					array(
						'type'        => 'email',
						'label'       => 'Email',
						'required'    => true,
						'placeholder' => 'jane@example.com',
					),
				),
				'settings'    => array(
					'gfm_con_type'        => 'message',
					'gfm_success_message' => 'Thank you for subscribing!',
					'gfm_submit_text'     => 'Subscribe',
					'gfm_submit_align'    => 'full',
				),
			),
			array(
				'slug'        => 'lead-generation',
				'name'        => esc_html__('Lead Generation', 'genform'),
				'description' => esc_html__('Capture leads with company, role, and interest fields for sales and marketing funnels.', 'genform'),
				'icon'        => 'dashicons-chart-line',
				'category'    => self::SLUG,
				'fields'      => array(
					array(
						'type'        => 'text',
						'label'       => 'Full Name',
						'required'    => true,
						'placeholder' => 'Your name',
					),
					array(
						'type'        => 'email',
						'label'       => 'Work Email',
						'required'    => true,
						'placeholder' => 'you@company.com',
					),
					array(
						'type'        => 'text',
						'label'       => 'Company',
						'required'    => true,
						'placeholder' => 'Company name',
					),
					array(
						'type'        => 'text',
						'label'       => 'Job Title',
						'required'    => false,
						'placeholder' => 'Your role',
					),
					array(
						'type'        => 'select',
						'label'       => 'Company Size',
						'required'    => true,
						'placeholder' => '',
						'options'     => array('1-10', '11-50', '51-200', '201-500', '500+'),
					),
					array(
						'type'        => 'select',
						'label'       => 'Interest',
						'required'    => true,
						'placeholder' => '',
						'options'     => array('Product Demo', 'Pricing Info', 'Partnership', 'General Inquiry'),
					),
				),
				'settings'    => array(
					'gfm_con_type'        => 'message',
					'gfm_success_message' => 'Lead captured successfully!',
					'gfm_submit_text'     => 'Get the Guide',
					'gfm_submit_align'    => 'full',
				),
			),
			array(
				'slug'        => 'event-registration',
				'name'        => esc_html__('Event Registration', 'genform'),
				'description' => esc_html__('Full event registration with attendee details, ticket type, and payment method.', 'genform'),
				'icon'        => 'dashicons-calendar',
				'category'    => self::SLUG,
				'fields'      => array(
					array(
						'type'        => 'text',
						'label'       => 'Full Name',
						'required'    => true,
						'placeholder' => 'Attendee name',
					),
					array(
						'type'        => 'email',
						'label'       => 'Email',
						'required'    => true,
						'placeholder' => 'your@email.com',
					),
					array(
						'type'        => 'tel',
						'label'       => 'Phone',
						'required'    => false,
						'placeholder' => 'Phone number',
					),
					array(
						'type'        => 'text',
						'label'       => 'Organization',
						'required'    => false,
						'placeholder' => 'Company or organization',
					),
					array(
						'type'        => 'select',
						'label'       => 'Ticket Type',
						'required'    => true,
						'placeholder' => '',
						'options'     => array('General Admission', 'VIP', 'Student', 'Early Bird'),
					),
					array(
						'type'     => 'checkbox',
						'label'    => 'Sessions of Interest',
						'required' => false,
						'options'  => array('Opening Keynote', 'Workshop A', 'Workshop B', 'Panel Discussion', 'Networking Mixer'),
					),
					array(
						'type'        => 'textarea',
						'label'       => 'Accessibility Needs',
						'required'    => false,
						'placeholder' => 'Let us know if you have any special requirements...',
					),
				),
				'settings'    => array(
					'gfm_con_type'        => 'message',
					'gfm_success_message' => 'Registration complete! Check your email for details.',
					'gfm_submit_text'     => 'Register for Webinar',
					'gfm_submit_align'    => 'full',
				),
			),
		);
	}
}
