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
		return array(
			array(
				'slug'        => 'patient-intake',
				'name'        => esc_html__('Patient Intake Form', 'genform'),
				'description' => esc_html__('Medical intake form with patient info, insurance, allergies, and health history.', 'genform'),
				'icon'        => 'dashicons-heart',
				'category'    => self::SLUG,
				'fields'      => array(
					array(
						'type'        => 'text',
						'label'       => 'Full Name',
						'required'    => true,
						'placeholder' => 'Patient name',
					),
					array(
						'type'        => 'email',
						'label'       => 'Email',
						'required'    => true,
						'placeholder' => 'patient@email.com',
					),
					array(
						'type'        => 'tel',
						'label'       => 'Phone',
						'required'    => true,
						'placeholder' => 'Phone number',
					),
					array(
						'type'        => 'date',
						'label'       => 'Date of Birth',
						'required'    => true,
						'placeholder' => '',
					),
					array(
						'type'        => 'select',
						'label'       => 'Gender',
						'required'    => true,
						'placeholder' => '',
						'options'     => array('Male', 'Female', 'Non-Binary', 'Prefer not to say'),
					),
					array(
						'type'        => 'text',
						'label'       => 'Insurance Provider',
						'required'    => false,
						'placeholder' => 'e.g. Blue Cross Blue Shield',
					),
					array(
						'type'        => 'text',
						'label'       => 'Policy Number',
						'required'    => false,
						'placeholder' => 'Insurance policy number',
					),
					array(
						'type'     => 'checkbox',
						'label'    => 'Known Allergies',
						'required' => false,
						'options'  => array('Penicillin', 'Aspirin', 'Latex', 'Shellfish', 'None'),
					),
					array(
						'type'        => 'textarea',
						'label'       => 'Current Medications',
						'required'    => false,
						'placeholder' => 'List any medications you\'re taking...',
					),
					array(
						'type'        => 'textarea',
						'label'       => 'Reason for Visit',
						'required'    => true,
						'placeholder' => 'Describe your symptoms or reason...',
					),
				),
				'settings'    => array(
					'gfm_con_type'        => 'message',
					'gfm_success_message' => 'Registration submitted securely.',
					'gfm_submit_text'     => 'Register Patient',
					'gfm_submit_align'    => 'full',
					'gfm_gdpr_enabled'    => '1',
					'gfm_gdpr_text'       => 'I consent to my data being processed for healthcare purposes.',
				),
			),
		);
	}
}
