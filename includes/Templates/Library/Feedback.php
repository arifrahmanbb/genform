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
		return array(
			array(
				'slug'        => 'customer-feedback',
				'name'        => esc_html__('Customer Feedback', 'genform'),
				'description' => esc_html__('Gather customer feedback with satisfaction rating, comments, and improvement suggestions.', 'genform'),
				'icon'        => 'dashicons-star-filled',
				'category'    => self::SLUG,
				'fields'      => array(
					array(
						'type'        => 'text',
						'label'       => 'Full Name',
						'required'    => false,
						'placeholder' => 'Optional — your name',
					),
					array(
						'type'        => 'email',
						'label'       => 'Email',
						'required'    => false,
						'placeholder' => 'Optional — for follow-up',
					),
					array(
						'type'        => 'select',
						'label'       => 'Overall Experience',
						'required'    => true,
						'placeholder' => '',
						'options'     => array('Excellent', 'Good', 'Average', 'Below Average', 'Poor'),
					),
					array(
						'type'     => 'radio',
						'label'    => 'Would you recommend us?',
						'required' => true,
						'options'  => array('Yes, definitely', 'Maybe', 'No'),
					),
					array(
						'type'        => 'textarea',
						'label'       => 'What did you enjoy most?',
						'required'    => false,
						'placeholder' => 'Tell us the highlights...',
					),
					array(
						'type'        => 'textarea',
						'label'       => 'How can we improve?',
						'required'    => false,
						'placeholder' => 'We value your suggestions...',
					),
				),
				'settings'    => array(
					'gfm_con_type'        => 'message',
					'gfm_success_message' => 'Feedback received! We appreciate your input.',
					'gfm_submit_text'     => 'Submit Feedback',
					'gfm_submit_align'    => 'full',
				),
			),
		);
	}
}
