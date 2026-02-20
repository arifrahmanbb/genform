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
		return array(
			array(
				'slug'        => 'support-ticket',
				'name'        => esc_html__('Support Ticket', 'genform'),
				'description' => esc_html__('Submit support requests with priority, department, and description fields for help desks.', 'genform'),
				'icon'        => 'dashicons-sos',
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
						'label'       => 'Email',
						'required'    => true,
						'placeholder' => 'you@example.com',
					),
					array(
						'type'        => 'select',
						'label'       => 'Department',
						'required'    => true,
						'placeholder' => '',
						'options'     => array('Sales', 'Technical Support', 'Billing', 'General Inquiry'),
					),
					array(
						'type'        => 'select',
						'label'       => 'Priority',
						'required'    => true,
						'placeholder' => '',
						'options'     => array('Low', 'Medium', 'High', 'Urgent'),
					),
					array(
						'type'        => 'text',
						'label'       => 'Subject',
						'required'    => true,
						'placeholder' => 'Brief summary of your issue',
					),
					array(
						'type'        => 'textarea',
						'label'       => 'Description',
						'required'    => true,
						'placeholder' => 'Please describe your issue in detail...',
					),
				),
				'settings'    => array(
					'gfm_con_type'        => 'message',
					'gfm_success_message' => 'Support request submitted. Our team will review it shortly.',
					'gfm_submit_text'     => 'Submit Ticket',
					'gfm_submit_align'    => 'full',
				),
			),
			array(
				'slug'        => 'job-application',
				'name'        => esc_html__('Job Application', 'genform'),
				'description' => esc_html__('Employment application form with personal details, position, experience, and availability.', 'genform'),
				'icon'        => 'dashicons-businessperson',
				'category'    => self::SLUG,
				'fields'      => array(
					array(
						'type'        => 'text',
						'label'       => 'Full Name',
						'required'    => true,
						'placeholder' => 'First and last name',
					),
					array(
						'type'        => 'email',
						'label'       => 'Email',
						'required'    => true,
						'placeholder' => 'candidate@email.com',
					),
					array(
						'type'        => 'tel',
						'label'       => 'Phone',
						'required'    => true,
						'placeholder' => 'Phone number',
					),
					array(
						'type'        => 'text',
						'label'       => 'Position Applied For',
						'required'    => true,
						'placeholder' => 'e.g. Marketing Manager',
					),
					array(
						'type'        => 'url',
						'label'       => 'LinkedIn / Portfolio',
						'required'    => false,
						'placeholder' => 'https://',
					),
					array(
						'type'        => 'select',
						'label'       => 'Experience Level',
						'required'    => true,
						'placeholder' => '',
						'options'     => array('Entry Level', '1-3 Years', '3-5 Years', '5-10 Years', '10+ Years'),
					),
					array(
						'type'        => 'select',
						'label'       => 'Start Availability',
						'required'    => true,
						'placeholder' => '',
						'options'     => array('Immediately', '2 Weeks', '1 Month', 'Flexible'),
					),
					array(
						'type'        => 'textarea',
						'label'       => 'Cover Letter',
						'required'    => false,
						'placeholder' => 'Tell us why you\'re a great fit...',
					),
				),
				'settings'    => array(
					'gfm_con_type'        => 'message',
					'gfm_success_message' => 'Your form has been submitted successfully!',
					'gfm_submit_text'     => 'Apply Now',
					'gfm_submit_align'    => 'full',
				),
			),
			array(
				'slug'        => 'request-quote',
				'name'        => esc_html__('Request a Quote', 'genform'),
				'description' => esc_html__('Quote request form for service-based businesses with budget range and project details.', 'genform'),
				'icon'        => 'dashicons-money-alt',
				'category'    => self::SLUG,
				'fields'      => array(
					array(
						'type'        => 'text',
						'label'       => 'Company Name',
						'required'    => true,
						'placeholder' => 'Your company',
					),
					array(
						'type'        => 'text',
						'label'       => 'Contact Name',
						'required'    => true,
						'placeholder' => 'Your full name',
					),
					array(
						'type'        => 'email',
						'label'       => 'Email',
						'required'    => true,
						'placeholder' => 'business@email.com',
					),
					array(
						'type'        => 'tel',
						'label'       => 'Phone',
						'required'    => false,
						'placeholder' => 'Phone number',
					),
					array(
						'type'        => 'select',
						'label'       => 'Service Needed',
						'required'    => true,
						'placeholder' => '',
						'options'     => array('Web Design', 'Web Development', 'SEO / Marketing', 'Branding', 'Consulting', 'Other'),
					),
					array(
						'type'        => 'select',
						'label'       => 'Budget Range',
						'required'    => true,
						'placeholder' => '',
						'options'     => array('Under $1,000', '$1,000 - $5,000', '$5,000 - $10,000', '$10,000 - $25,000', '$25,000+'),
					),
					array(
						'type'        => 'textarea',
						'label'       => 'Project Details',
						'required'    => true,
						'placeholder' => 'Describe your project requirements...',
					),
				),
				'settings'    => array(
					'gfm_con_type'        => 'message',
					'gfm_success_message' => 'Thank you for your request. We will provide a quote soon.',
					'gfm_submit_text'     => 'Get My Quote',
					'gfm_submit_align'    => 'full',
				),
			),
			array(
				'slug'        => 'bug-report',
				'name'        => esc_html__('Bug Report', 'genform'),
				'description' => esc_html__('Software bug report form with severity, steps to reproduce, and environment details.', 'genform'),
				'icon'        => 'dashicons-warning',
				'category'    => self::SLUG,
				'fields'      => array(
					array(
						'type'        => 'text',
						'label'       => 'Your Name',
						'required'    => true,
						'placeholder' => 'Reporter name',
					),
					array(
						'type'        => 'email',
						'label'       => 'Email',
						'required'    => true,
						'placeholder' => 'reporter@email.com',
					),
					array(
						'type'        => 'text',
						'label'       => 'Bug Title',
						'required'    => true,
						'placeholder' => 'Short summary of the bug',
					),
					array(
						'type'        => 'select',
						'label'       => 'Severity',
						'required'    => true,
						'placeholder' => '',
						'options'     => array('Critical', 'Major', 'Minor', 'Cosmetic'),
					),
					array(
						'type'        => 'text',
						'label'       => 'Browser / OS',
						'required'    => false,
						'placeholder' => 'e.g. Chrome 120 on Windows 11',
					),
					array(
						'type'        => 'textarea',
						'label'       => 'Steps to Reproduce',
						'required'    => true,
						'placeholder' => "1. Go to...\n2. Click on...\n3. Observe...",
					),
					array(
						'type'        => 'textarea',
						'label'       => 'Expected vs Actual',
						'required'    => true,
						'placeholder' => 'What should happen vs. what actually happens...',
					),
				),
				'settings'    => array(
					'gfm_con_type'        => 'message',
					'gfm_success_message' => 'Bug report submitted. Our development team will investigate.',
					'gfm_submit_text'     => 'Report Bug',
					'gfm_submit_align'    => 'full',
				),
			),
		);
	}
}
