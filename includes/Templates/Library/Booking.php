<?php

/**
 * Booking Form Templates
 *
 * Appointment and reservation form templates for restaurants,
 * service providers, and hotels.
 *
 * @package GenForm
 * @since   1.1.0
 */

namespace GenForm\Templates\Library;

if (! defined('ABSPATH')) {
	exit;
}

final class Booking
{


	/**
	 * Category slug used for grouping.
	 */
	public const SLUG = 'booking';

	/**
	 * Return all templates in this category.
	 *
	 * @return array<int, array<string, mixed>>
	 */
	public static function get(): array
	{
		return array(
			array(
				'slug'        => 'restaurant-booking',
				'name'        => esc_html__('Restaurant Reservation', 'genform'),
				'description' => esc_html__('Table reservation form with date, time, guest count, and special requests for restaurants.', 'genform'),
				'icon'        => 'dashicons-food',
				'category'    => self::SLUG,
				'fields'      => array(
					array(
						'type'        => 'text',
						'label'       => 'Full Name',
						'required'    => true,
						'placeholder' => 'Guest name',
					),
					array(
						'type'        => 'email',
						'label'       => 'Email',
						'required'    => true,
						'placeholder' => 'email@example.com',
					),
					array(
						'type'        => 'tel',
						'label'       => 'Phone Number',
						'required'    => true,
						'placeholder' => '+1 (555) 000-0000',
					),
					array(
						'type'        => 'date',
						'label'       => 'Reservation Date',
						'required'    => true,
						'placeholder' => '',
					),
					array(
						'type'        => 'select',
						'label'       => 'Time Slot',
						'required'    => true,
						'placeholder' => '',
						'options'     => array('11:00 AM', '12:00 PM', '1:00 PM', '5:00 PM', '6:00 PM', '7:00 PM', '8:00 PM', '9:00 PM'),
					),
					array(
						'type'        => 'select',
						'label'       => 'Number of Guests',
						'required'    => true,
						'placeholder' => '',
						'options'     => array('1', '2', '3', '4', '5', '6', '7', '8', '10+'),
					),
					array(
						'type'        => 'textarea',
						'label'       => 'Special Requests',
						'required'    => false,
						'placeholder' => 'Allergies, highchair, birthday setup, etc.',
					),
				),
				'settings'    => array(
					'gfm_con_type'        => 'message',
					'gfm_success_message' => 'Reservation received! We will follow up soon.',
					'gfm_submit_text'     => 'Reserve Table',
					'gfm_submit_align'    => 'full',
				),
			),
			array(
				'slug'        => 'appointment-booking',
				'name'        => esc_html__('Appointment Booking', 'genform'),
				'description' => esc_html__('Service appointment scheduling form with service type, preferred date, and notes.', 'genform'),
				'icon'        => 'dashicons-calendar-alt',
				'category'    => self::SLUG,
				'fields'      => array(
					array(
						'type'        => 'text',
						'label'       => 'Full Name',
						'required'    => true,
						'placeholder' => 'Your full name',
					),
					array(
						'type'        => 'email',
						'label'       => 'Email',
						'required'    => true,
						'placeholder' => 'you@domain.com',
					),
					array(
						'type'        => 'tel',
						'label'       => 'Phone',
						'required'    => true,
						'placeholder' => 'Contact number',
					),
					array(
						'type'        => 'select',
						'label'       => 'Service',
						'required'    => true,
						'placeholder' => '',
						'options'     => array('General Consultation', 'Follow-up Visit', 'Initial Assessment', 'Other'),
					),
					array(
						'type'        => 'date',
						'label'       => 'Preferred Date',
						'required'    => true,
						'placeholder' => '',
					),
					array(
						'type'        => 'select',
						'label'       => 'Time Preference',
						'required'    => true,
						'placeholder' => '',
						'options'     => array('Morning (9-12)', 'Afternoon (12-5)', 'Evening (5-8)'),
					),
					array(
						'type'        => 'textarea',
						'label'       => 'Notes',
						'required'    => false,
						'placeholder' => 'Anything we should know beforehand?',
					),
				),
				'settings'    => array(
					'gfm_con_type'        => 'message',
					'gfm_success_message' => 'Your request is submitted! We will confirm shortly via email or phone.',
					'gfm_submit_text'     => 'Book Now',
					'gfm_submit_align'    => 'full',
				),
			),
			array(
				'slug'        => 'hotel-reservation',
				'name'        => esc_html__('Hotel Reservation', 'genform'),
				'description' => esc_html__('Room booking form with check-in/out dates, room type, and guest preferences.', 'genform'),
				'icon'        => 'dashicons-building',
				'category'    => self::SLUG,
				'fields'      => array(
					array(
						'type'        => 'text',
						'label'       => 'Guest Name',
						'required'    => true,
						'placeholder' => 'Full name',
					),
					array(
						'type'        => 'email',
						'label'       => 'Email',
						'required'    => true,
						'placeholder' => 'guest@email.com',
					),
					array(
						'type'        => 'tel',
						'label'       => 'Phone',
						'required'    => true,
						'placeholder' => 'Contact phone',
					),
					array(
						'type'        => 'date',
						'label'       => 'Check-in Date',
						'required'    => true,
						'placeholder' => '',
					),
					array(
						'type'        => 'date',
						'label'       => 'Check-out Date',
						'required'    => true,
						'placeholder' => '',
					),
					array(
						'type'        => 'select',
						'label'       => 'Room Type',
						'required'    => true,
						'placeholder' => '',
						'options'     => array('Standard Single', 'Standard Double', 'Deluxe Suite', 'Family Room', 'Penthouse'),
					),
					array(
						'type'        => 'number',
						'label'       => 'Number of Guests',
						'required'    => true,
						'placeholder' => '2',
					),
					array(
						'type'        => 'textarea',
						'label'       => 'Special Requests',
						'required'    => false,
						'placeholder' => 'Late check-in, extra bedding, etc.',
					),
				),
				'settings'    => array(
					'gfm_con_type'        => 'message',
					'gfm_success_message' => 'Reservation submitted! You\'ll receive a confirmation email shortly.',
					'gfm_submit_text'     => 'Reserve Now',
					'gfm_submit_align'    => 'full',
				),
			),
		);
	}
}
