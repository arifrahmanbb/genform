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
		return array(
			array(
				'slug'        => 'course-enrollment',
				'name'        => esc_html__('Course Enrollment', 'genform'),
				'description' => esc_html__('Student enrollment form with personal info, course selection, and prerequisites.', 'genform'),
				'icon'        => 'dashicons-welcome-learn-more',
				'category'    => self::SLUG,
				'fields'      => array(
					array(
						'type'        => 'text',
						'label'       => 'Full Name',
						'required'    => true,
						'placeholder' => 'Student name',
					),
					array(
						'type'        => 'email',
						'label'       => 'Email',
						'required'    => true,
						'placeholder' => 'student@email.com',
					),
					array(
						'type'        => 'tel',
						'label'       => 'Phone',
						'required'    => true,
						'placeholder' => 'Contact number',
					),
					array(
						'type'        => 'date',
						'label'       => 'Date of Birth',
						'required'    => true,
						'placeholder' => '',
					),
					array(
						'type'        => 'select',
						'label'       => 'Course',
						'required'    => true,
						'placeholder' => '',
						'options'     => array('Web Development', 'Graphic Design', 'Digital Marketing', 'Data Science', 'Business Management'),
					),
					array(
						'type'        => 'select',
						'label'       => 'Education Level',
						'required'    => true,
						'placeholder' => '',
						'options'     => array('High School', 'Associate', 'Bachelor\'s', 'Master\'s', 'PhD'),
					),
					array(
						'type'     => 'radio',
						'label'    => 'Schedule Preference',
						'required' => true,
						'options'  => array('Full-Time', 'Part-Time', 'Online / Self-Paced'),
					),
					array(
						'type'        => 'textarea',
						'label'       => 'Why are you interested?',
						'required'    => false,
						'placeholder' => 'Tell us about your goals...',
					),
				),
				'settings'    => array(
					'gfm_con_type'        => 'message',
					'gfm_success_message' => 'Registration complete! We will review your application securely.',
					'gfm_submit_text'     => 'Submit Application',
					'gfm_submit_align'    => 'full',
				),
			),
		);
	}
}
